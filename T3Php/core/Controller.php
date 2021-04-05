<?php
/**
 * 控制器基类
 */
namespace T3Php\core;

class Controller
{
    /**
     * @var object 验证器对象
     * 在$this->validate()方法中实例化得到，其实不建议这种将对象装到属性中再链试调用的方式，因为不方便在IDE中找到具体实现过程的方法。
     * 如果大量这种实现方式，类属性嵌套层次太深，可能会混乱到调用者根本找不到方法在哪里实现的。
     * 好的实现方式应该以IDE工具能轻松定位为准。
     * 反面教材。
     */
    public $validate;

    /**
     * 构造方法
     */
    public function __construct()
    {
    }

    /**
     * 执行验证器验证
     * @param array $data 需要验证的数据
     * @param string $validateClass 验证器类
     */
    public function validate($data, $validateClass)
    {
        $this->validate = new Validate();
        $this->validate->setRule($validateClass);
        $result = $this->validate->check($data);
        return $result;
    }
}
