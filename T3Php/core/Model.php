<?php
/**
 * 数据库驱动
 */
namespace T3Php\core;

class Model
{
    /**
     * @var array 单例模型容器
     */
    private static $models = [];


    public function __construct()
    {

    }

    /**
     * 单例模型，方便模型之间使用 modelName::model()->调用方法
     */
    public static function model($className = __class__)
    {
        if(isset(self::$models[$className]))
            return self::$models[$className];
        else {
            $model = self::$models[$className] = new $className();
            return $model;
        }
    }




}

