<?php
/**
 * Redis操作类
 */

namespace T3Php\dirvers;
require_once ROOT . "/T3Php/helpers/T3PhpErrorCode.php";
use T3Php\helpers\T3PhpErrorCode;
use T3Php\core\Db;

class Redis
{
    /**
     * @var array  T3Php\core\Db里面链接的redis对象
     */
    public static $redis;
    /**
     * @var array 构造本类对象
     */
    public static $selfObj;
    /**
     * @var array redis配置
     */
    public static $config;

    /**
     * @param string $db 传入databases.php::redis配置项的key
     * @return mixed
     */
    public static function db($key = null)
    {
        // 默认取第一个redis的配置做连接
        if (!$key) {
            foreach (Db::$dbConfig as $k => $item) {
                if ($item['type'] == 'redis') {
                    $key          = $k;
                    self::$config = $item;
                    break;
                }
            }
        }
        // 单例
        if (isset(self::$selfObj))
            return self::$selfObj;
        else {
            self::$redis   = Db::$db[$key];
            self::$selfObj = new self();
            return self::$selfObj;
        }
    }

    /**
     * string 取值
     * @param string $key
     * @return array
     */
    public function get($key)
    {
        $value =  self::$redis->get(self::$config['prefix'] . $key);
        $value = json_decode($value, true);
        return $value;
    }

    /**
     * string 写入
     * @param string $key
     * @param string|array|object $value
     * @param int $expire
     * @return array
     */
    public function set($key, $value, $expire = 0)
    {
        if (empty($key) || empty($value)) {
            return [null, '写入数据不能为空', T3PhpErrorCode::FAILED];
        }
        // 统一以Json格式存入
        $value = json_encode($value, 320);
        try {
            self::$redis->set(self::$config['prefix'] . $key, $value, $expire);
        } catch (\Exception $e) {
            return [$e->getMessage(), '写入失败', T3PhpErrorCode::FAILED];
        }
        return [null, 'success', T3PhpErrorCode::SUCCESS];
    }


    /**
     * 获取所有key
     * @return array
     */
    public function getAllKeys()
    {
        return self::$redis->keys(self::$config['prefix'] . '*');
    }

    /**
     * string 删除
     * @param string $key
     * @return bool
     */
    public function del($key)
    {
        return self::$redis->del(self::$config['prefix'] . $key);
    }

    /**
     * string 同步Redis
     * @param string $key
     * @param array $data
     * @param string $expire
     * @return array
     */
    public function syncRedis($key, $data, $expire = 0)
    {
        if (empty($key) || empty($data) || !is_array($data)) {
            return [null, '参数不能为空', T3PhpErrorCode::FAILED];
        }
        try {
            $cache    = self::get($key);
            $cacheArr = json_decode($cache, true);
            $dataArr  = array_merge($cacheArr, $data);
            return self::set($key, $dataArr, $expire);
        } catch (\Exception $e) {
            return [$e->getMessage(), 'Redis更新失败', T3PhpErrorCode::FAILED];
        }
    }


}

