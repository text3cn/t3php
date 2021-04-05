<?php
/**
 * 视频控制器
 */
namespace app\index\controller;


use T3Php\core\HttpController;
use T3Php\core\T3;

class Index extends HttpController
{

    public function index()
    {
        $indexHtml = '<style type="text/css">*{ padding: 0; margin: 0; }
                .face{display:inline-block;
                transform:rotate(88deg);
                -ms-transform:rotate(88deg); 	/* IE 9 */
                -moz-transform:rotate(88deg); 	/* Firefox */
                -webkit-transform:rotate(88deg); /* Safari 和 Chrome */
                -o-transform:rotate(88deg); 	/* Opera */}
                div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } 
                body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color:#80c342;font-size:18px;} 
                h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } 
                p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;">
                <h1>PHP</h1>
                <p>PHP是世界上最好的语言<br/>
                <span style="font-size:30px">Hello World ！ <span class="face">  :)</span></span></p></div>
                <div>T3Php 轻量级PHP开发框架</div>
                ';
        T3::app()->view($indexHtml, 200);
    }





}
