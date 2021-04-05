<?php
/**
 *  type 支持 mysql、mongodb、redis、elastic_search
 */
return [
    'mysql_video_main' => [
        'type'     => 'mysql',
        'host'     => 'localhost',
        'port'     => 3306,
        'user'     => 'root',
        'password' => '123456',
        'dbName'   => 'test',
    ],
    'redis'            => [
        'type'     => 'redis',
        'host'     => 'localhost',
        'port'     => 6379,
        'password' => '',
        'timeout'  => 30,
        // 几号库
        'select'   => 1,
        // 缓存前缀
        'prefix'   => '',
        // 全局缓存有效期 0表示永久缓存
        'expire'   => 0,
    ],
    'mongodb_1'        => [
        'type'     => 'mongodb',
        'host'     => 'localhost',
        'port'     => 27017,
        'user'     => '',
        'password' => '',
        'dbName'   => 'test',
    ],
    'es'               => [
        'type'     => 'es',
        'host'     => 'localhost',
        'port'     => 9200,
        'scheme'   => 'http',
        'username' => false,
        'password' => false,
    ],
];
