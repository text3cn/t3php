<?php
/**
 * 框架内置全局函数
 * @author T3
 * @since 2020-03-16
 *
 */

    /**
     * CLI模式 控制台输出
     * @param mixed $data 要输出的数据
     * @param mixed $dump 是否var_dump输出
     */
    if (function_exists('fun_name')) {
        function p($data, $dump = false)
        {
            echo '>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>';
            echo chr(10) . chr(10);
            print_r($data);
            echo chr(10) . chr(10);
            echo '>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>';
            echo chr(10);
            echo chr(10);
        }
    }

    /**
     * CLI模式 控制台输出 - 开始
     * @param mixed $data 要输出的数据
     * @param mixed $dump 是否var_dump输出
     */

        function p1($data)
        {
            echo '>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>';
            echo chr(10) . chr(10);
            print_r($data);
            echo chr(10);
        }


    /**
     * CLI模式 控制台输出 - 结束
     * @param mixed $data 要输出的数据
     * @param mixed $dump 是否var_dump输出
     */

        function p2($data, $dump = false)
        {
            echo chr(10);
            print_r($data);
            echo chr(10) . chr(10);
            echo '>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>';
            echo chr(10);
            echo chr(10);
        }

