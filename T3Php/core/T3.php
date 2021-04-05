<?php
/**
 * 初始化完成后将一些常用资源装进T3对象供开发者使用
 */
namespace T3Php\core;


class T3
{
    public static $T3;
    public $config;  // 基础配置 application/config/config.php
    public $db;  // 数据库连接对象
    public $dbConfig; // 数据库配置
    public $module; // 当前请求访问的模块

    /**
     * 通过 T3::app() 调用，以保持单例
     */
    public static function app()
    {
        if(!self::$T3){
            self::$T3 = new self();
        }
        return self::$T3;
    }

    /**
     * 根据数据库配置项key返回数据库连接对象
     * 调用：T3::app()->db('mongodb_1')
     * @param $db
     * @return object
     */
    public function db($db){
        return $this->db[$db];
    }

    /**
     * 获取第一个配置项的mysql
     * 调用：T3::app()->mysql()
     * @return object
     */
    public function mysql(){
        foreach($this->dbConfig as $key => $item){
            if($item['type'] == 'mysql'){
                return $this->db[$key];
            }
        }
    }

    /**
     * 获取第一个配置项的mongodb
     * 调用：T3::app()->mongodb()
     * 驱动：T3Php\core\Drivers\Mongodb
     * @return object
     */
    public function mongodb(){
        foreach($this->dbConfig as $key => $item){
            if($item['type'] == 'mongodb'){
                return $this->db[$key];
            }
        }
    }

    /**
     * 获取第一个配置项的es
     * 调用：T3::app()->es()
     * 驱动：T3Php\core\Drivers\ElasticSearch
     * @return object
     */
    public function es(){
        foreach($this->dbConfig as $key => $item){
            if($item['type'] == 'es'){
                return $this->db[$key];
            }
        }
    }

    /**
     * 获取第一个配置项的redis
     * 调用：T3::app()->redis()
     * @return object
     */
    public function redis(){
        foreach($this->dbConfig as $key => $item){
            if($item['type'] == 'redis'){
                return $this->db[$key];
            }
        }
    }


    /**
     * 返回响应信息 api json数据
     */
    public function response($data, $message = 'success', $errorCode = 0, $header = ['Content-Type' => 'text/html; charset=UTF-8'])
    {
        if (empty($data)) {
            $ret['data']  = (object)[];
        } else {
            $ret['data']  = $data;
        }
        $ret['message']   = $message;
        $ret['errorCode'] = $errorCode;
        echo json_encode($ret, 320);
    }

    /**
     * 渲染浏览器页面
     * @param string  $html 发送html代码
     * @param int     $httpCode 发送http状态码
     */
    public function view($html, $httpCode = 200){
        if ($httpCode == 404){
            header('HTTP/1.1 404 Not Found');
        }
        echo $html;
        exit;
    }

    /**
     * 发送cookie
     * @param string $key   cookie key
     * @param mixed  $value cookie value
     */
    public function setCookie($key, $vlue){
        $response = new Response(200, [], 'This Is T3Php Set Cookie');
        $response->cookie($key, $vlue);
        $this->httpConnection->send($response);
    }

    /**
     * 获取/设置cookie
     * @param string $key   cookie key
     * @param mixed  $value cookie value
     */
    public function cookie($key = null, $value = null){
        // 设置
        if($value){
            $this->setCookie($key, $value);
        }
        // 获取
        else{
            if($key) {
                return $this->requset->cookie($key);
            }else{
                return $this->requset->cookie();
            }
        }

    }


    /**
     * 存储session
     * @param string $key   cookie key
     * @param mixed  $value cookie value
     */
    public function session($key = null, $value = null){
        // 设置
        if($value){
            $this->httpRequest->session()->set($key, $value);
        }
        // 获取
        else{
            if($key){

            }else{
                // 获取整个session数组
                $session = $this->httpRequest->session();
                $all = $session->all();
                return $all();
            }
        }

    }

}

