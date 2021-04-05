<?php
/**
 * 程序初始化
 */

namespace T3Php\core;

// 加载框架预置全局函数、常量
require_once ROOT . '/T3Php/helpers/Functions.php';
require_once ROOT . '/T3Php/helpers/Constants.php';
// 加载用户全局函数、常量
require_once ROOT . '/application/common/functions.php';
require_once ROOT . '/application/common/constants.php';


class Init
{
    /**
     * @var array 配置文件 application/config/config.php
     */
    public $config;

    public function run()
    {
        require_once ROOT . '/T3Php/core/Controller.php';
        require_once ROOT . '/T3Php/core/HttpController.php';
        require_once ROOT . '/T3Php/core/Db.php';
        require_once ROOT . '/T3Php/core/Model.php';
        require_once ROOT . '/T3Php/core/T3.php';
        require_once ROOT . '/T3Php/core/Validate.php';
        T3::app()->config = $this->config = require_once ROOT . '/application/config/config.php';
        T3::app()->dbConfig = require_once ROOT . '/application/config/databases.php';
        // 自动加载控制、模型
        spl_autoload_register(function ($class) {
            $class = '' . substr($class, 3);
            $path  = ROOT . '/application/modules' . str_replace('\\', '/', $class) . '.php';
            if (is_file($path)) {
                require_once $path;
            }
        });
        $this->router();

    }

    /**
     * http路由
     */
    public function router()
    {
        // 链接数据库
        Db::connectDb();
        // 加载application层级文件，common 等
        spl_autoload_register(function ($class) {
            $class = '' . substr($class, 3);
            $path  = ROOT . '/application' . str_replace('\\', '/', $class) . '.php';
            if (is_file($path)) {
                require_once $path;
            }
        });
        // 接收请求，处理框架路由
        if (empty($_SERVER["REQUEST_URI"])) {
            return;
        }
        $route = explode('/', $_SERVER["REQUEST_URI"]);
        $route = array_values(array_filter($route));
        if (isset($route[count($route) - 2])) {
            $count             = count($route);
            $routeLast         = explode('?', $route[$count - 1]);
            $route[$count - 1] = $routeLast[0];
            // 方法
            $method            = $route[$count - 1];
            // 控制器
            $controller        = ucfirst(self::camelName($route[count($route) - 2]));
            // 模块
            $module = '';

            for ($i = 0; $i < $count - 2; $i++) {
                $module .= $route[$i] . DIRECTORY_SEPARATOR;
            }
            $module           = substr($module, 0, -1);
            // 加载控制器并执行方法
            $controllerFile = ROOT . '/application/modules/' . $module . '/controller/' . $controller . '.php';
            // 如果控制器不存在，尝试REST风格以请求方式作为方法查找URL找路径
            if (!is_file($controllerFile)) {
                for ($i = 0; $i < $count - 1; $i++) {
                    $module .= $route[$i] . DIRECTORY_SEPARATOR;
                }
                // 重组控制器、方法
                $module         = substr($module, 0, -1);
                $controller     = ucfirst(self::camelName($route[count($route) - 1]));
                $controllerFile = ROOT . '/application/modules/' . $module . '/controller/' . $controller . '.php';
                $method = strtolower($_SERVER["REQUEST_METHOD"]);
                if (!is_file($controllerFile)){
                    // 控制器不存在
                    T3::app()->view('404 Not Found', 404);
                }
            }

            T3::app()->module = $module;
            require_once $controllerFile;
            $C      = "\\app\\" . str_replace('/', '\\', $module) . '\controller\\' . $controller;
            $C      = new $C( );
            $method = self::camelName($method);
            if (!method_exists($C, $method)) {
                T3::app()->view('404 Not Found', 404);

                return;
            }
            $C->$method();
            exit;
        }
        // 默认首页
        if (\PHP_SAPI !== 'cli') {
            $controllerFile = ROOT . '/application/modules/index/controller/Index.php';
            require_once $controllerFile;
            $C  = "app\index\controller\index";
            $method = "index";
            $C      = new $C();
            $C->$method();
        }
    }

    /**
     * 分隔符转为驼峰式名称
     * @param string $name 方法名称
     * @param string $separator 分隔符
     * @param bool   $came true.大驼峰 false.小驼峰
     * @return string
     */
    public static function camelName($name, $separator = '-', $came = true)
    {
        $camelName = $name;
        $nameArr   = explode($separator, $name);
        if (isset($nameArr[1])) {
            $count     = count($nameArr);
            $camelName = $nameArr[0];
            for ($i = 1; $i < $count; $i++) {
                $camelName .= $nameArr[$i];
            }
            if ($came) {
                return ucfirst($camelName);
            } else {
                return lcfirst($camelName);
            }
        }
        return $camelName;
    }


}


