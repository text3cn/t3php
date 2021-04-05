<?php
// workerman支持
namespace T3Php\core;

use Workerman\Worker;

if (!defined("ROOT")) {
    define("ROOT", dirname(dirname(__DIR__)));
    require_once ROOT . '/T3Php/core/Init.php';
    $init = new init();
    $init->run();
}

require_once  ROOT  . '/vendor/workerman/Autoloader.php';



class Workerman
{
    public function route($connection, $request){
        T3::app()->workermanConnection = $connection;
        T3::app()->workermanRequest = $request;
        $route = explode('/', $request->path());
        if(isset($route[1]) && $route[1] == "favicon.ico"){
            return;
        }
        // 路由处理
        if ($route[count($route) - 2]) {
            array_shift($route);
            $count             = count($route);
            $routeLast         = explode('?', $route[$count - 1]);
            $route[$count - 1] = $routeLast[0];
            // 方法
            $method            = $route[$count - 1];
            // 控制器
            $controller        = ucfirst(Init::camelName($route[count($route) - 2]));
            // 模块
            $module = '';
            for ($i = 0; $i < $count - 2; $i++) {
                $module .= $route[$i] . DIRECTORY_SEPARATOR;
            }
            $module           = substr($module, 0, -1);
            // 加载控制器并执行方法
            $controllerFile = ROOT . '/application/modules/' . $module . '/controller/' . $controller . '.php';
            if (!is_file($controllerFile)) {
                // 如果控制器不存在，尝试REST风格URL找路径
                for ($i = 0; $i < $count - 1; $i++) {
                    $module .= $route[$i] . DIRECTORY_SEPARATOR;
                }
                // 重组控制器、方法
                $module         = substr($module, 0, -1);
                $controller     = ucfirst(Init::camelName($route[count($route) - 1]));
                $controllerFile = ROOT . '/application/modules/' . $module . '/controller/' . $controller . '.php';
                $method = strtolower($request->method());
                if (!is_file($controllerFile)){
                    p('控制器不存在');
                }
            }

            T3::app()->module = $module;
            require_once $controllerFile;
            $C      = "\\app\\" . str_replace('/', '\\', $module) . '\controller\\' . $controller;
            $C      = new $C($connection, $request);
            $method = Init::camelName($method);
            if (!method_exists($C, $method)) {
                p('方法不存在');
                return;
            }
            $C->$method();
            $connection->send('');
        }
        // 默认首页
        $connection->send('');
    }

}




