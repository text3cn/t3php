<?php
/**
 * 数据库驱动
 */
namespace T3Php\core;

class Db
{
    /**
     * @var array 数据库连接对象
     */
    public static $db;
    public static $dbConfig;


    public function __construct()
    {

    }

    /**
     * 连接数据库
     */
    public static function connectDb(){
        // 单例
        if(!self::$db) {
            self::$dbConfig = T3::app()->dbConfig;
            require_once ROOT . '/T3Php/dirvers/Mysql.php';
            // 将db实例存储在全局变量中(也可以存储在某类的静态成员中)
            foreach (self::$dbConfig as $key => $item) {
                if ($item['type'] == 'mysql') {
                    $db[$key] = new \T3Php\core\dirvers\Connection(
                        $item['host'],
                        $item['port'],
                        $item['user'],
                        $item['password'],
                        $item['dbName']
                    );
                }
                if ($item['type'] == 'redis') {
                    require_once ROOT . '/T3Php/dirvers/Redis.php';
                    $redis = new \Redis();
                    $redis->connect($item['host'], $item['port'], $item['timeout']);
                    if ($item['password']) {
                        $redis->auth($item['password']);
                    }
                    // 指定库
                    $redis->select($item['select']);
                    $db[$key] = $redis;
                }
                if ($item['type'] == 'mongodb') {
                    if ($item['user'] && $item['password']) {
                        $connString = "mongodb://{$item['user']}:{$item['password']}@{$item['host']}:{$item['port']}";
                    } else {
                        $connString = "mongodb://{$item['host']}:{$item['port']}";
                    }
                    require_once ROOT . '/T3Php/dirvers/Mongodb.php';
                    $db[$key] = new \T3Php\dirvers\Mongodb($connString, $item);
                }
                if ($item['type'] == 'es') {
                    $params = [
                        'host'   => $item['host'],
                        'port'   => $item['port'],          // 端口：默认9200
                        'scheme' => $item['scheme'],        // scheme：默认http
                        'user'   => $item['username'],      // 用户名
                        'pass'   => $item['password'],      // 密码
                    ];
                    $db[$key] = $params;
                }
            }
            self::$db = $db;
            //装进T3对象
            T3::app()->db = $db;
            T3::app()->dbConfig = self::$dbConfig;
        }
    }




}

