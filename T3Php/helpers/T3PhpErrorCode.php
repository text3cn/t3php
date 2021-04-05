<?php
/**
 * 错误码
 * @author T3
 * @since  2020-02-27
 */

namespace T3Php\helpers;

class T3PhpErrorCode
{
    /**
     * 1开头 公共消息类
     */

    /**
     * 成功
     */
    const SUCCESS = 0;
    /**
     * 失败
     */
    const FAILED = 1;


    /**
     * 缺少token参数
     */
    const LACK_TOKEN = 10010;
    /**
     * 无效TOKEN
     */
    const INVALID_TOKEN = 10011;
    /**
     * API版本号错误
     */
    const API_VERSION_ERROR = 10020;
    /**
     * 请求方式错误
     */
    const METHOD_ERROR = 10030;
    /**
     * 参数验证失败
     */
    const PARAM_ERROR = 10040;


    /**
     * 数据库操作失败
     */
    const MYSQL_ERROR = 20000;
    /**
     * mysql插入记录失败
     */
    const MYSQL_INSERT_FAIL = 20001;
    /**
     * mysql更新记录失败
     */
    const MYSQL_UPDATE_FAIL = 20002;
    /**
     * mysql删除记录失败
     */
    const MYSQL_DELETE_FAIL = 20003;
    /**
     * mysql记录重复
     */
    const MYSQL_RECORD_REPET = 20004;
    /**
     * 模型验证失败
     */
    const MYSQL_RECORD_NOT_EXIST = 20005;

    /**
     * Redis写入失败
     */
    const REDIS_WRITE_FAIL = 21000;
    /**
     * Redis写入失败
     */
    const REDIS_READ_FAIL = 21001;


}

