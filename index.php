<?php
/**
 * 应用入口文件
 * @author T3
 * @since 2020-03-14
 */
namespace T3Php\core;

define('ROOT', __DIR__);
require_once ROOT . '/T3Php/core/Init.php';
$init = new init();
$init->run();

