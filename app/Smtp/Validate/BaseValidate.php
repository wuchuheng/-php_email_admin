<?php
/**
 * 自定义验证器约束规范.
 * Author Wuchuheng<wuchuheng@163.com>
 * Licence MIT
 * DATE 2020/3/9
 */
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Di\Annotation\Inject;
use phpDocumentor\Reflection\Types\Array_;

abstract class BaseValidate
{
    /**
     * @Inject()
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

    /**
     * @var 最终验证规则存储.
     */
    private $rules;

    /**
     * 场景名
     * @var Array
     */
    public $scene;

    /**
     * 定义验证规则l
     * @return array
     */
    abstract  public function rules(): array;

    /**
     * 定义错误消息.
     * @return array
     */
    abstract public function messages():array;

    /**
     * 定义验证场景.
     *
     * @scene_name 场景名
     * @return array
     */
    public function scene(string $scene_name): array
    {

        // 场景规则
        $rules = array_key_exists($scene_name, $this->rules()) ? $this->rules()[$scene_name] : [];
        $extend_rules = array_key_exists($scene_name, $this->sceneExtendRules()) ? $this->sceneExtendRules()[$scene_name] : [];
        $this->rules = array_merge($rules, $extend_rules);
        return $this;
    }

    /**
     * 场景附加规则.
     * @return array
     */
   public function sceneExtendRules(): array
   {
       return  [];
   }

    /**
     * 验证.
     */
   public function goCheck(): void
   {

   }
}
