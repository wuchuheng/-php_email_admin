<?php

/**
 * this file for smtp connnect event.
 *
 * @author wuchuheng <root@wuchuheng.com>
 *
 */
namespace App\Smtp;

use  Hyperf\JsonRpc\TcpServer;
use Swoole\Server as SwooleServer;
use Hyperf\Utils\Context;
use App\Smtp\Service\ServerResponse;
use App\Smtp\Validate\{
    CheckStatus,
    CheckHelo
};
use App\Exception\SmtpBaseException;
use App\Exception\FooException;
use Hyperf\Contract\ConfigInterface;
use Throwable;
use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;

use Hyperf\Contract\DispatcherInterface;
use Hyperf\Contract\MiddlewareInitializerInterface;
use Hyperf\Contract\OnReceiveInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\CoreMiddlewareInterface;
use Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler;
use Hyperf\Rpc\Context as RpcContext;
use Hyperf\Rpc\Protocol;
use Hyperf\Server\ServerManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;


class Server extends TcpServer
{
    /**
     * init connect session status.
     *
     */
    public $is_helo = false;

    /**
     * SMTP connet
     *
     */
    public function onConnect(SwooleServer $Server, int $fd)
    {
        $Server->send($fd, ServerResponse::welcome());
    }

    /**
     * SMTP receive
     *
     */
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
                $this->send($server, $fd, $response);
            }
        }

	/* throw new FooException('Foo Exception...', 800); */
        /* throw new SmtpBaseException([ */
        /*     'msg' => '1', */
        /*     'code' => 2, */
        /*     'fd' => $fd */
        /* ]); */

        /* $data = substr($data, 0, -2); */
        /* // is the data legal. */
        
        /* $session_info = CheckStatus::getSessionStatus($this, $data); */ 
        /* $session_info = CheckStatus::getSessionStatus($this, $data); */ 
        /* switch($session_info['status']){ */
        /*     case 'HELO': */
        /*         $this->is_helo || $this->is_helo = true; */
        /*         $Server->send($fd, "{$session_info['code']} {$session_info['message']}"); */
        /*         break; */
        /*     case 'MAILI FROM': */
        /*         $Server->send($fd, "250 Mail OK \r\n"); */
        /*         // TODO ... */
        /*         break; */
        /*     case 'RCPT TO': */
        /*         // TODO ... */
        /*         break; */
        /*     case 'DATA': */
        /*         // TODO ... */
        /*         break; */
        /*     case 'QUIT': */
        /*         // TODO ... */
        /*         break; */
        /*     case 'REST': */
        /*         // TODO ... */
        /*         break; */
        /*     case 'VRFY': */
        /*         // TODO ... */
        /*         break; */
        /*     case 'EXPN': */
        /*         // TODO ... */
        /*         break; */
        /*     case 'HELP': */
        /*         // TODO ... */
        /*         break; */
        /*     case 'ERROR': */
        /*         $Server->send($fd, "{$session_info['code']} {$session_info['message']}"); */
        /*         break; */
        /*     case 'SYNTAX': */
        /*         $Server->send($fd, "{$session_info['code']} {$session_info['message']}"); */
        /*         break; */
        /* } */
    }

    /**
     * SMTP close
     *
     */
    public function onClose(SwooleServer $Server, int $from_id, int $reactor_id)
    {
        var_dump("close:\n");
        // TODO ...
    }

}
