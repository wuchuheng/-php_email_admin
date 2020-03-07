<?php

/**
 * this file for smtp connnect event.
 *
 * @author wuchuheng <root@wuchuheng.com>
 *
 */

namespace App\Smtp;

use App\Smtp\Util\Session;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\DispatcherInterface;
use Hyperf\Contract\MiddlewareInitializerInterface;
use Hyperf\Contract\OnReceiveInterface;
use Hyperf\Contract\PackerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Hyperf\HttpMessage\Server\Request as Psr7Request;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\HttpServer\Contract\CoreMiddlewareInterface;
use Hyperf\JsonRpc\CoreMiddleware;
use Hyperf\JsonRpc\Exception\Handler\TcpExceptionHandler;
use Hyperf\JsonRpc\ResponseBuilder;
use Hyperf\Rpc\Protocol;
use Hyperf\Rpc\ProtocolManager;
use Hyperf\RpcServer\RequestDispatcher;
use Hyperf\Server\Exception\InvalidArgumentException;
use Hyperf\Server\ServerManager;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Swoole\Server as SwooleServer;
use Hyperf\Rpc\Context as RpcContext;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Throwable;
use Hyperf\HttpServer\Annotation\Middlewares;
use  App\Smtp\MiddleWare\SmtpWriteMiddleWare;
use Hyperf\Di\Annotation\Inject;


class Server  implements OnReceiveInterface, MiddlewareInitializerInterface
{
    /**
     * @Inject()
     * @var \Hyperf\Contract\SessionInterface
     */
    private $session;

    /**
     * @var \Hyperf\JsonRpc\ResponseBuilder
     */
    protected $responseBuilder;

    /**
     * @var PackerInterface
     */
    protected $packer;

    /**
     * @var ProtocolManager
     */
    protected $protocolManager;

    /**
     * @var array
     */
    protected $serverConfig;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var DispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var ExceptionHandlerDispatcher
     */
    protected $exceptionHandlerDispatcher;

    /**
     * @var array
     */
    protected $middlewares;

    /**
     * @var CoreMiddlewareInterface
     */
    protected $coreMiddleware;

    /**
     * @var array
     */
    protected $exceptionHandlers;

    /**
     * @var string
     */
    protected $serverName;

    /**
     * @var Protocol
     */
    protected $protocol;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    private $Session;

    public function __construct(
        ContainerInterface $container,
        RequestDispatcher $dispatcher,
        ExceptionHandlerDispatcher $exceptionDispatcher,
        ProtocolManager $protocolManager,
        StdoutLoggerInterface $logger
    )
    {
        $this->container = $container;
        $this->dispatcher = $dispatcher;
        $this->exceptionHandlerDispatcher = $exceptionDispatcher;
        $this->logger = $logger;
        $this->protocolManager = $protocolManager;
        $this->Session  = $this->container->get(Session::class);
    }

    public function initCoreMiddleware(string $serverName): void
    {
        $this->initServerConfig($serverName);

        $this->initProtocol();

        $this->serverName = $serverName;
        $this->coreMiddleware = $this->createCoreMiddleware();

        $config = $this->container->get(ConfigInterface::class);
        $this->middlewares = $config->get('middlewares.' . $serverName, []);
        $this->exceptionHandlers = $config->get('exceptions.handler.' . $serverName, $this->getDefaultExceptionHandler());
    }

    protected function initProtocol()
    {
        $protocol = 'jsonrpc';
        if ($this->isLengthCheck()) {
            $protocol = 'jsonrpc-tcp-length-check';
        }

        $this->protocol = new Protocol($this->container, $this->protocolManager, $protocol, $this->serverConfig);
        $this->packer = $this->protocol->getPacker();
        $this->responseBuilder = make(ResponseBuilder::class, [
            'dataFormatter' => $this->protocol->getDataFormatter(),
            'packer' => $this->packer,
        ]);
    }

    protected function isLengthCheck(): bool
    {
        return boolval($this->serverConfig['settings']['open_length_check'] ?? false);
    }

    protected function initServerConfig(string $serverName): array
    {
        $servers = $this->container->get(ConfigInterface::class)->get('server.servers', []);
        foreach ($servers as $server) {
            if ($server['name'] === $serverName) {
                return $this->serverConfig = $server;
            }
        }
        throw new InvalidArgumentException(sprintf('Server name %s is invalid.', $serverName));
    }

    protected function buildRequest(int $fd, int $fromId, string $data): ServerRequestInterface
    {
        $tmp = $data;
        $data = is_array($this->packer->unpack($data)) ? $this->packer->unpack($data) : ['jsonrpc' => '2.0'];
        $data['data'] = $tmp;
        return $this->buildJsonRpcRequest($fd, $fromId, $data);
    }

    protected function buildJsonRpcRequest(int $fd, int $fromId, array $data)
    {
        if (!isset($data['method'])) {
            $data['method'] = '';
        }
        if (!isset($data['params'])) {
            $data['params'] = [];
        }
        /** @var \Swoole\Server\Port $port */
        [$type, $port] = ServerManager::get($this->serverName);

        $uri = (new Uri())->withPath($data['method'])->withHost($port->host)->withPort($port->port);
        $request = (new Psr7Request('POST', $uri))->withAttribute('fd', $fd)
            ->withAttribute('fromId', $fromId)
            ->withAttribute('data', $data)
            ->withAttribute('request_id', $data['id'] ?? null)
            ->withParsedBody($data['params'] ?? '');

        $this->getContext()->setData($data['context'] ?? []);

        if (!isset($data['jsonrpc'])) {
            return $this->responseBuilder->buildErrorResponse($request, ResponseBuilder::INVALID_REQUEST);
        }
        return $request;
    }

    protected function getDefaultExceptionHandler(): array
    {
        return [
            TcpExceptionHandler::class,
        ];
    }

    protected function buildResponse(int $fd, SwooleServer $server): ResponseInterface
    {
        $response = new Psr7Response();
        return $response->withAttribute('fd', $fd)->withAttribute('server', $server);
    }

    protected function createCoreMiddleware(): CoreMiddlewareInterface
    {
        return new CoreMiddleware($this->container, $this->protocol, $this->responseBuilder, $this->serverName);
    }



    protected function send(SwooleServer $server, int $fd, ResponseInterface $response, string $data = ''): void
    {
        if ($dir = getDirectiveByMsg($data)) {
            $Session = $this->Session;
            $Session->set($fd, 'prev_dir', $dir);
        }
        $server->send($fd, (string)$response->getBody());
    }


    public function onConnect(SwooleServer $server, int $fd)
    {
        $app_name = $this->container->get(ConfigInterface::class)->get('app_name');
        $welcome = "220 welcome to {$app_name} System.";
        $welcome = smtp_pack($welcome);
        $server->send($fd, $welcome);
    }

    public function onReceive(SwooleServer $server, int $fd, int $fromId, string $data): void
    {
        $request = $response = null;
        try {
            // Initialize PSR-7 Request and Response objects.
            Context::set(ServerRequestInterface::class, $request = $this->buildRequest($fd, $fromId, $data));
            Context::set(ResponseInterface::class, $this->buildResponse($fd, $server));

            // $middlewares = array_merge($this->middlewares, MiddlewareManager::get());
            $middlewares = $this->middlewares;

            $request = $this->coreMiddleware->dispatch($request);

            $response = $this->dispatcher->dispatch($request, $middlewares, $this->coreMiddleware);
        } catch (Throwable $throwable) {
            // Delegate the exception to exception handler.
            $exceptionHandlerDispatcher = $this->container->get(ExceptionHandlerDispatcher::class);
            $response = $exceptionHandlerDispatcher->dispatch($throwable, $this->exceptionHandlers);
        } finally {
            if (! $response || ! $response instanceof ResponseInterface) {
                $response = $this->transferToResponse($response);
            }
            if ($response) {
                $this->send($server, $fd, $response, $data);
            }
        }
    }

    public function onClose(SwooleServer $Server, int $from_id, int $reactor_id)
    {
        // 清空会话数据
        $Session = $this->Session;
        $Session->removeAllByFd($from_id);
    }

    protected function transferToResponse($response): ?ResponseInterface
    {
        $psr7Response = Context::get(ResponseInterface::class);
        if ($psr7Response instanceof ResponseInterface) {
            return $psr7Response->withBody(new SwooleStream($response));
        }
        return null;
    }


    protected function getContext()
    {
        return $this->container->get(RpcContext::class);
    }
}
