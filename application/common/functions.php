<?php
    /**
     * 全局变量、函数
     */

    /**
     * debug调试函数，HTML格式化输出print_r
     * @param unknown $data 要输出的数据
     * @param unknown $exit 是否exit
     * @param unknown $dump 是否var_dump输出
     */
    function p($data, $dump = false, $exit = true ){
        // CLI模式
        if(substr(PHP_SAPI_NAME(),0,3) == 'cli'){
            echo '>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>';
            echo  chr(10) . chr(10); print_r($data); echo chr(10) . chr(10);
            echo '>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>';
            echo chr(10);
            echo chr(10);
            if($exit){
                return;
            }
        }else {
            $colors = ['#59B909', '#FF2D2D', '#FF359A', '#F75000', '#9F4D95', '#3C3C3C', '#00A0F4',
                '#3696fa', '#80c342', "#F60", "#66c7a2", "#3ac05e", '#ab85d5'];
            $color = $colors[rand(0, count($colors) - 1)];
            echo '<style>body{background:#f3f3f3;}</style>';
            echo '<pre style="color:', $color, ';font-weight:900;font-size:18px;text-shadow: -2px 1px 3px rgba(60, 60, 60, 0.1);">';
            if ($dump) {
                var_dump($data);
            } else {
                print_r($data);
            }
            if ($exit) {
                exit;
            }
            echo '</pre>';
        }
    }

    /**
     * 资源返回公共函数
     */
    function response($data,  $message = null, $errorCode = 0)
    {
        $return['errorCode'] = $errorCode;
        $return['message']   = $message;
        if (empty($data)) {
            $return['data']  = (object)[];
        } else {
            $return['data']  = $data;
        }
        exit(json_encode($return, 320));
    }

    /**
     * 发送HTTP请求
     * @param string $url 请求地址
     * @param string $method 请求方式 GET/POST
     * @param string $refererUrl 请求来源地址
     * @param array/json $data 发送数据，数组或者json数据
     * @param string $httpHeader
     * @param int $timeout
     * @param bool $proxy
     * @return boolean
     */
    function sendRequest($url, $data, $method = 'GET', $httpHeader = array('Content-Type:application/json'), $timeout = 30, $refererUrl = '', $proxy = false)
    {
        $ch = null;
        if('POST' === strtoupper($method)) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER,0 );
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            if ($refererUrl) {
                curl_setopt($ch, CURLOPT_REFERER, $refererUrl);
            }
            if($httpHeader) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
            }
            if(is_string($data)){
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        } else if('GET' === strtoupper($method)) {
            if(is_string($data)) {
                $realUrl = $url. (strpos($url, '?') === false ? '?' : ''). $data;
            } else {
                $realUrl = $url. (strpos($url, '?') === false ? '?' : ''). http_build_query($data);
            }

            $ch = curl_init($realUrl);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            if ($refererUrl) {
                curl_setopt($ch, CURLOPT_REFERER, $refererUrl);
            }
        } else {
            return false;
        }

        if($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }

    /**
     * curl发送POST数据请求
     * @param string $url   请求链接
     * @param string $data  要POST的数据，json格式
     * @return array $arr   请求返回
     */
    function post_curl($url,$data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json_str = curl_exec($ch);
        $arr = json_decode($json_str, true);
        return $arr;
    }

    /**
     * @abstract 写入日志文件
     * @param $content mixed 写入文件的内容
     * @param string $path   写入文件路径
     * @param string $fileName 文件名
     * file_put_contents()  把字符串写入文件中。
     * var_export 数组转字符串  var_export($content, true)
     * PHP_EOL      PHP换行符，这个变量会根据平台而变
     * FILE_APPEND  在文件末尾追加写入数据
     * $content .= "\n"; unix换行  $content .= "\r\n";  windows换行  $content .= "\r";  mac换行
     */
    function wlog($content, $fileName = '',  $path = './logs/')
    {
        if (empty($fileName)) {
            $fileName = date('Y-m-d').'.log';
        }
        if (is_array($content)) {
            $content = var_export($content, true);
        }
        $content = date('Y-m-d H:i:s').' '.$content;

        file_put_contents($path.$fileName, $content.PHP_EOL, FILE_APPEND);
    }

    /**
     * 建立跳转请求表单
     * @param string $url 数据提交跳转到的URL
     * @param array $data 请求参数数组
     * @param string $method 提交方式：post或get 默认post
     * @return string 提交表单的HTML文本
     */
    function buildRequestForm($url, $data, $method = 'post')
    {
        $sHtml = "<form id='requestForm' name='requestForm' action='".$url."' method='".$method."'>";
        foreach($data as $key => $val)
        {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."' />";
        }
        $sHtml = $sHtml."<input type='submit' value='确定' style='display:none;'></form>";
        $sHtml = $sHtml."<script>document.forms['requestForm'].submit();</script>";
        return $sHtml;
    }

    /**
     * 格式化模型数据库
     * @param  object $model 模型数据
     * @return object/array 格式化后可读写的数据
     */
    function modelToArray(&$model){
        $json  = json_encode($model);
        $model = json_decode($json);
        $model = (array)$model;
        $temp = [];
        //分页数据筛选
        if(isset($model['data'])){
            foreach($model['data'] as &$item){
                $temp[] = (array)$item;
            }
            $model['data'] = $temp;
        }
    }

    /**
     * 对象转数组
     */
    function to_array(&$data){
        $json = json_encode($data);
        $data = json_decode($json);
        $data = (array)$data;
        foreach($data as &$item){
            if(is_object($item)){
                to_array($item);
            }
        }
    }

    /**
     * 无乱码截取中文字符串
     * @param string $str   要截取的字符串
     * @param int    $start 截取开始位置
     * @param int    $over  截取结束位置
     * @return string
     */
    function c_str($str, $start, $over) {
        $lenth = $start + $over;
        for($i = 0; $i < $lenth; $i++) {
            if (ord ( substr ( $str, $i, 1 ) ) > 0xa0) {
                $okstr .= substr ( $str, $i, 2 );
                $i ++;
            } else {
                $okstr .= substr ( $str, $i, 1 );
            }
        }
        return $okstr;
    }

    /**
     * 无乱码截取中文字符串
     * @param string $str    要截取的字符串
     * @param int    $start  截取开始位置
     * @param int    $lenth  截取长度
     * @param int    $pure   富文本字符串是否去除换行转为纯文本字符串再截取
     * @param string $code   字符编码
     * @return string
     */
    function cn_substr($string, $start, $lenth, $dot = true, $pure = true, $code = 'utf-8') {
        if($pure){
            $string = getRawText($string);
            $string = preg_replace('#\s|\r|\n|\n\r|" "|\x0B|\0|\t#','',$string);
        }
        if($dot && ($lenth < utf8_strlen($string))){
            $lenth -= 1;
        }
        // 使用mb_substr库截取
        if (function_exists('mb_substr')) {
            $str = mb_substr($string, $start, $lenth, $code);
        }
        // 拼接省略号
        if($dot && ($lenth < utf8_strlen($string))){
            $str .= '...';
        }
        return $str;
    }

    // 计算中文字符串长度
    function utf8_strlen($string = null) {
        // 将字符串分解为单元
        preg_match_all("/./us", $string, $match);
        // 返回单元个数
        return count($match[0]);
    }

    /**
     * 从富文本内容中获取纯文本
     * @param $richText
     * @return string
     */
    function getRawText($richText)
    {
        $content = trim(strip_tags($richText)); // 去除 HTML 和 PHP 标记，去除首尾空白符
        $content = preg_replace('/^(&nbsp;)+/', '', $content); // 去除首部空格实体字符 &nbsp;
        $content = preg_replace('/(&nbsp;)+$/', '', $content); // 去除尾部空格实体字符 &nbsp;
        return $content;
    }

    /**
     * 格式化金额
     * @param number $amount 金额
     */
    function formatAmount($amount)
    {
        if (is_numeric($amount)) {
            return number_format($amount, 2, '.', ''); // 保留两位，'.'作为小数点，千位符为空
        } else {
            return 0.00;
        }
    }

    /**
     * 判断请求是否来自于微信浏览器
     */
    function is_weixin()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        }
        return false;
    }

    /**
     * @ 判断是否移动端
     * @return boolean
     */
    function isMobile() {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 如果有HTTP_USER_AGENT有Mobile字样一定是移动设备
        if(strpos($_SERVER['HTTP_USER_AGENT'],'Mobile')!==false){
            return true;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是微信
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile','MicroMessenger');
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取文件大小字符串表示
     * @param integer @fileSize 文件大小整数值
     * @return string 文件大小字符串
     */
    function getFileSizeString($fileSize)
    {
        $file_size = $fileSize;
        if ($file_size >= 1099511627776)
            $show_filesize = number_format(($file_size / 1099511627776), 2) . " TB";
        elseif ($file_size >= 1073741824)
            $show_filesize = number_format(($file_size / 1073741824), 2) . " GB";
        elseif ($file_size >= 1048576)
            $show_filesize = number_format(($file_size / 1048576), 2) . " MB";
        elseif ($file_size >= 1024)
            $show_filesize = number_format(($file_size / 1024), 2) . " KB";
        elseif ($file_size > 0)
            $show_filesize = $file_size . " b";
        elseif ($file_size == 0 || $file_size == -1)
            $show_filesize = "0 b";
        return $show_filesize;
    }

    /**
     * 构造树数据
     * @param object $list 无限极列表数据二维数组
     */
    function generateTree($list){
        //索引重1开始重建
        $index = 1;
        foreach($list as $item){
            $items[$index] = $item;
            $index++;
        }
        //引用构造树数据
        $tree = array();
        foreach($items as $item){
            if(isset($items[$item['pid']])){
                $items[$item['pid']]['subNode'][] = &$items[$item['id']];
            }else{
                $tree[] = &$items[$item['id']];
            }
        }
        return $tree;
    }

    //去除数组空值
    function array_remove_empty($data){
        $ret = [];
        foreach ($data as $key => $item){
            if(!empty($item)){
                $ret[$key] = $item;
            }
        }
        return $ret;
    }

    /**
     * @abstract 以万为单位格式化数字，保留两位小数
     * @param int $number 原数字
     */
    function tenThousandFormate($number){
        if($number >= 10000){
            $number = $number / 10000;
            $number = number_format($number, 2, '.', '');
            $number = floatval($number); // 去掉小数后面的0
            $number = $number . '万';
        }
        return $number;
    }

    /**
     * 传入日期格式或时间戳格式时间，返回与当前时间的差距，如1分钟前，2小时前，5月前，3年前等
     * @param string or int $date 分两种日期格式"2013-12-11 14:16:12"或时间戳格式"1386743303"
     * @param int $type 1为时间戳格式，2为date时间格式
     * @return string
     */
    function formatAgoTime($date = 0, $type = 1) {
        if(empty($date)){
            return '';
        }
        date_default_timezone_set('PRC'); //设置成中国的时区
        switch ($type) {
            case 1:
                //$date时间戳格式
                $second = time() - $date;
                $minute = floor($second / 60) ? floor($second / 60) : 1; //得到分钟数
                if ($minute >= 60 && $minute < (60 * 24)) { //分钟大于等于60分钟且小于一天的分钟数，即按小时显示
                    $hour = floor($minute / 60); //得到小时数
                } elseif ($minute >= (60 * 24) && $minute < (60 * 24 * 30)) { //如果分钟数大于等于一天的分钟数，且小于一月的分钟数，则按天显示
                    $day = floor($minute / ( 60 * 24)); //得到天数
                } elseif ($minute >= (60 * 24 * 30) && $minute < (60 * 24 * 365)) { //如果分钟数大于等于一月且小于一年的分钟数，则按月显示
                    $month = floor($minute / (60 * 24 * 30)); //得到月数
                } elseif ($minute >= (60 * 24 * 365)) { //如果分钟数大于等于一年的分钟数，则按年显示
                    $year = floor($minute / (60 * 24 * 365)); //得到年数
                }
                break;
            case 2:
                //$date为字符串格式 2013-06-06 19:16:12
                $date = strtotime($date);
                $second = time() - $date;
                $minute = floor($second / 60) ? floor($second / 60) : 1; //得到分钟数
                if ($minute >= 60 && $minute < (60 * 24)) { //分钟大于等于60分钟且小于一天的分钟数，即按小时显示
                    $hour = floor($minute / 60); //得到小时数
                } elseif ($minute >= (60 * 24) && $minute < (60 * 24 * 30)) { //如果分钟数大于等于一天的分钟数，且小于一月的分钟数，则按天显示
                    $day = floor($minute / ( 60 * 24)); //得到天数
                } elseif ($minute >= (60 * 24 * 30) && $minute < (60 * 24 * 365)) { //如果分钟数大于等于一月且小于一年的分钟数，则按月显示
                    $month = floor($minute / (60 * 24 * 30)); //得到月数
                } elseif ($minute >= (60 * 24 * 365)) { //如果分钟数大于等于一年的分钟数，则按年显示
                    $year = floor($minute / (60 * 24 * 365)); //得到年数
                }
                break;
            default:
                break;
        }
        if (isset($year)) {
            return $year . '年前';
        } elseif (isset($month)) {
            return $month . '月前';
        } elseif (isset($day)) {
            return $day . '天前';
        } elseif (isset($hour)) {
            return $hour . '小时前';
        } elseif (isset($minute)) {
            return $minute . '分钟前';
        }
    }

    /**
     * @abstract 获取设备
     */
    function getDevice(){
        preg_match('/\(.*\)/U', $_SERVER['HTTP_USER_AGENT'], $device);
        $device = str_replace('(', '', $device);
        $device = str_replace(')', '', $device);
        return $device[0];
    }

    /**
     * 更新url地址栏get参数
     * @param string    $param 参数名 , 数组方式
     *  [
     *      'add' => [['param1', 'value1'], ['param2', 'value2']],  //也兼容一维数组
     *      'delete' => 'param1,param2']  //多个逗号隔开
     * @param {unknown} $value 参数值
     * @param string    $type  操作类型，add 增加参数， delete 删除参数
     * @return $url 更新后的url，不含域名
     */
    function urlParam($param, $value = '', $type = 'add'){
        $questionIndex = strpos($_SERVER['REQUEST_URI'], '?');
        $router = $questionIndex ? substr($_SERVER['REQUEST_URI'], 0, $questionIndex) : $_SERVER['REQUEST_URI'];
        parse_str( $_SERVER['QUERY_STRING'], $params);
        /*更新参数*/
        // 数组形式多个参数操作
        if(is_array($param)){
            foreach($param as $key => $item){
                switch($key){
                    case 'add':
                        if(isset($param['add'][1][0])){
                            foreach($param['add'] as $item2){
                                $params[$item2[0]] = $item2[1];
                            }
                        }else{
                            $params[$param['add'][0]] = $param['add'][1];
                        }
                        break;
                    case 'delete':
                        $deleteParamas  = explode(',', $param['delete']);
                        foreach($deleteParamas as $item2){
                            unset($params[$item2]);
                        }
                        break;
                }
            }
        }
        // key=>value 参数形式单个参数操作
        else{
            if($type == 'add'){
                $params[$param] = $value;
            }elseif($type == 'delete'){
                unset($params[$param]);
            }
        }
        $url = $router . '?' . http_build_query($params);
        return $url;
    }
    /**
     * @abstract 获取IP
     * @return string
     */
    function getIp()
    {
        if(!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        } else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (!empty($_SERVER["REMOTE_ADDR"])) {
            $cip = $_SERVER["REMOTE_ADDR"];
        } else {
            $cip = '';
        }
        preg_match("/[\d\.]{7,15}/", $cip, $map); $cip = isset($map[0]) ? $map[0] : 0;
        return $cip;
    }

    /**
     * @abstract 写入日志文件
     * @param $content mixed 写入文件的内容
     * @param string $path   写入文件路径
     * @param string $fileName 文件名
     * file_put_contents()  把字符串写入文件中。
     * var_export 数组转字符串  var_export($content, true)
     * PHP_EOL      PHP换行符，这个变量会根据平台而变
     * FILE_APPEND  在文件末尾追加写入数据
     * $content .= "\n"; unix换行  $content .= "\r\n";  windows换行  $content .= "\r";  mac换行
     */
    function writeLog($content, $path = './Log/', $fileName = '')
    {
        if (empty($fileName)) {
            $fileName = date('Y-m-d').'.log';
        }
        if (is_array($content)) {
            $content = var_export($content, true);
        }
        $content = date('Y-m-d H:i:s').PHP_EOL.$content;

        file_put_contents($path.$fileName, $content.PHP_EOL.PHP_EOL, FILE_APPEND);
    }

    /**
     * @abstract 手机号码验证
     * @param $mobile
     * @return bool
     */
    function verifyMobile($mobile)
    {
        if (!preg_match("/^1[3456789]{1}\d{9}$/", $mobile)) {
            return false;
        }
        return true;
    }

    /**
     * @abstract 判断图片链接是否有效
     * @param $url
     * @return bool
     */
    function img_exits($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, 1); // 不下载
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (curl_exec($ch) !== false) {
            return true;
        }else {
            return false;
        }
    }

    /**
     * @abstract 过滤字符串emoji表情
     * @param $string
     * @return mixed
     */
    function filterEmoji($string)
    {
        $string = preg_replace_callback('/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $string);
        return $string;
    }

    /**
     * @abstract 校验字符串是否包含emoji表情
     * @param $string
     * @return bool true 存在 false 不存在
     */
    function verifyEmoji($string)
    {
        $text = json_encode($string);                   //暴露出unicode
        return preg_match("/(\\\u[ed][0-9a-f]{3})/i", $text);
    }

    /**
     * @abstract 敏感词校验
     * @param array $data 定义敏感词一维数组
     * @param string $string 要过滤的内容
     * @param bool $operation 存在敏感词是否替换 true.是 false.否
     * @param string $replace 替换内容
     */
    function sensitiveCheck($data, $string, $operation = false, $replace = '*')
    {
        $pattern = "/" . implode("|", $data) . "/i";        //定义正则表达式
        if (preg_match_all($pattern, $string, $matches)) {  //匹配到了结果
            $list = $matches[0];
            if ($operation) {
                $replaceArray  = array_combine($list, array_fill(0, count($list), $replace));  //匹配的数组合并
                $stringAfter   = strtr($string, $replaceArray);                                //结果替换
                return $stringAfter;
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * @abstract 验证是否为Json数据
     * @param string $string 待验证字符串
     * @return bool
     */
    function is_json($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * @abstract 判断是否cli访问
     */
    function is_cli_mode() {
        $sapi_type = php_sapi_name();
        if (isset($sapi_type) && substr($sapi_type, 0, 3) == 'cli') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $string 要加密的字符串
     * @param string $operation DECODE 解密， ENCODE 加密
     * @param string $key 秘钥
     * @param int $expiry 密文有效期，单位秒
     * @return false|string
     */
    function tokenCode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        if ($operation == 'DECODE') {
            $string = str_replace(['-', '_', '.'], ['+', '/', '='], $string);
        }
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
        $ckey_length = 4;
        // 密匙
        $key = md5($key ? $key : $GLOBALS['discuz_auth_key']);
        // 密匙a会参与加解密
        $keya = md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证
        $keyb = md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) :
            substr(md5(microtime()), -$ckey_length)) : '';

        // 参与运算的密匙
        $cryptKey   = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptKey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
        //解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $string        = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
            sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);
        $result        = '';
        $box           = range(0, 126);
        $randKey       = [];
        // 产生密匙簿
        for ($i = 0; $i <= 126; $i++) {
            $randKey[$i] = ord($cryptKey[$i % $key_length]);
            //   ECHO ord($cryptKey[$i % $key_length]) , "<BR>";
        }

        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
        for ($j = $i = 0; $i < 127; $i++) {
            $j       = ($j + $box[$i] + $randKey[$i]) % 127;
            $tmp     = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        // 核心加解密部分
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a       = ($a + 1) % 127;
            $j       = ($j + $box[$a]) % 127;
            $tmp     = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 127]));
            // echo mb_chr(ord(  $string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]  )) , '<br>';
        }

        if ($operation == 'DECODE') {
            // 验证数据有效性，请看未加密明文的格式
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
                substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
            return $keyc . str_replace(['+', '/', '='], ['-', '_', '.'], base64_encode($result));
        }
    }

    /**
     * 生成指定长度随机字符串
     * @param number $length
     * @return string
     */
    function createNonceStr($length = 16) {
        $chars  = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str    = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    /**
     * @abstract 把秒数转换为时分秒的格式
     * @param Int $times 时间，单位 秒
     * @return String
     */
    function secondToTime($times, $h = ':', $m = ':', $s = ':'){
        $result = '';
        if ($times>0) {
            $hour = floor($times/3600);
            $minute = floor(($times-3600 * $hour)/60);
            $second = floor((($times-3600 * $hour) - 60 * $minute) % 60);
            if($hour){
                $result = $hour. $h;
            }
            if($minute){
                $result .= $minute. $m;
            }
            $result .= $second . $s;
        }else{
            $result = '0' . $s;
        }
        return $result;
    }

    /**
     * getEndDayByMouth 根据月份获取最后一天
     * @return 最后一天
     */
    function getLastDayByMouth($year, $month)
    {
        return date('t', strtotime($year . '-' . $month . '-' . '01'));
    }

    /**
     * 根据月份获取季度
     * @param int $month 月份
     * @return number
     */
    function getQuarterByMonth($month){
        $Q = ceil($month/3);
        return $Q;
    }

    /**
     * 获取指定年的起止时间
     * @param int $year 年, 不传则取当前年
     */
    function getYearRangeTime($year = null){
        if(!$year){
            $year = date('Y');
        }
        $time['startTime'] = $year . '-01-01 00:00:00';
        $time['endTime']   = $year . '-12-31 59:59:59';
        return $time;
    }

    /**
     * 根据datetime获取其月份起止时间
     * @param int $date datetime格式, 不传则取当前月
     */
    function getMonthRangeTime($date = null){
        if($date == null){
            $year  = date('Y');
            $month = date('m');
            $lastDay = getLastDayByMouth($year, $month);
        }
        $time['startTime'] = $year . '-' . $month .'-01 00:00:00';
        $time['endTime']   = $year . '-' . $month .'-'. $lastDay .' 59:59:59';
        return $time;
    }

    /**
     * 根据datetime获取其季度起止时间
     * @param int $date datetime格式, 不传则取当前季度
     */
    function getQuarterRangeTime($date = null){
        if($date == null){
            $quarter = ceil(date('m') / 3);
            $time['startTime'] = date('Y-m-d H:i:s', mktime(0, 0, 0, $quarter*3-3+1, 1, date('Y')));
            $time['endTime']   = date('Y-m-d H:i:s', mktime(23, 59, 59, $quarter*3, date('t', mktime(0, 0 , 0, $quarter*3, 1, date("Y"))), date('Y')));
        }
        return $time;
    }

    /**
     * 根据两个时间点，获取中间的月份
     */
    function getTimeLong($startDate, $endDate)
    {

        $startArr = explode("-", $startDate);
        $endArr = explode("-", $endDate);

        $startYear = intval($startArr[0]);
        $startMonth = intval($startArr[1]);
        $startDay = intval($startArr[2]);

        $endYear = intval($endArr[0]);
        $endMonth = intval($endArr[1]);
        $endDay = intval($endArr[2]);


        $diffYear = $endYear - $startYear;

        $monthArr = [];
        //获取月份
        if ($diffYear == 0) {
            for ($month = $startMonth; $month <= $endMonth; $month++) {
                if ($month == $startMonth) {
                    $monthArr[] = $startYear . '-' . $month . '-' . $startDay;
                } else
                    if ($month == $endMonth) {
                        $monthArr[] = $startYear . '-' . $month . '-' . $endDay;
                    } else {
                        $monthArr[] = $startYear . '-' . $month . '-1';
                    }
            }
        } else {
            for ($year = $startYear; $year <= $endYear; $year++) {
                if ($year == $startYear) {
                    for ($month = $startMonth; $month <= 12; $month++) {
                        if ($month == $startMonth) {
                            $monthArr[] = $year . '-' . $month . '-' . $startDay;
                        } else {
                            $monthArr[] = $year . '-' . $month . '-1';
                        }
                    }
                } elseif ($year == $endYear) {
                    for ($month = 1; $month <= $endMonth; $month++) {
                        if ($month == $endMonth) {
                            $monthArr[] = $year . '-' . $month . '-' . $endDay;
                        } else {
                            $monthArr[] = $year . '-' . $month . '-1';
                        }
                    }
                } else {
                    for ($month = 1; $month <= 12; $month++) {
                        $monthArr[] = $year . '-' . $month . '-1';
                    }
                }
            }
        }
        return $monthArr;
    }

    /**
     *function：计算两个日期相隔多少年，多少月，多少天
     *@param string $date1[格式如：2011-11-5]
     *@param string $date2[格式如：2012-12-01]
     *@return array array('年','月','日');
     *@author wanggh
     */
    function diffDateDate($date1, $date2)
    {
        if (strtotime($date1) > strtotime($date2)) {
            $tmp = $date2;
            $date2 = $date1;
            $date1 = $tmp;
        }
        list($Y1, $m1, $d1) = explode('-', $date1);
        list($Y2, $m2, $d2) = explode('-', $date2);
        $Y = $Y2 - $Y1;
        $m = $m2 - $m1;
        $d = $d2 - $d1;
        if ($d < 0) {
            $d += (int)date('t', strtotime("-1 month $date2"));
            $m--;
        }
        if ($m < 0) {
            $m += 12;
            $Y--;
        }
        return array(
            'year' => $Y,
            'month' => $m,
            'day' => $d);
    }

    /**
     * 时间比较函数，返回两个日期相差几秒、几分钟、几小时或几天
     * @param $dateBegin开始时间
     * @param $dateEnd结束时间
     * @param $unit相差的时间类型
     */
    function dateDiff($dateBegin, $dateEnd, $unit = "")
    {
        switch ($unit) {
            case 's': //相差秒
                $dividend = 1;
                break;
            case 'i': //相差分
                $dividend = 60;
                break;
            case 'h': //相差小时
                $dividend = 3600;
                break;
            case 'd': //相差天
                $dividend = 86400;
                break;
            case 'y': //相差年
                $dividend = 31536000;
                break;
            default:
                $dividend = 86400;
        }
        $timeBegin = strtotime($dateBegin);
        $timeEnd = strtotime($dateEnd);
        if ($timeBegin && $timeEnd) {
            return (float)($timeEnd - $timeBegin) / $dividend;
        } else {
            return false;
        }
    }

    /**
     * @生成13位毫秒时间戳
     */
    function milli_stamptime() {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * @ unicode 转汉字
     */
    function unicode_decode($unicode_str){
        $json = '{"str":"'.$unicode_str.'"}';
        $arr = json_decode($json,true);
        if(empty($arr)) return '';
        return $arr['str'];
    }

    /**
     * @ unicode 汉字转unicode
     */
    function unicode_encode($name)
    {
        $name = iconv('UTF-8', 'UCS-2', $name);
        $len = strlen($name);
        $str = '';
        for ($i = 0; $i < $len - 1; $i = $i + 2)
        {
            $c = $name[$i];
            $c2 = $name[$i + 1];
            if (ord($c) > 0)
            {  // 两个字节的文字
                $str .= '\u'.base_convert(ord($c), 10, 16).base_convert(ord($c2), 10, 16);
            }
            else
            {
                $str .= $c2;
            }
        }
        return $str;
    }

    /**
     * 驼峰命名转换为下划线命名
     * SalesOrder -> sales_order
     * 驼峰命名为，所有单词首字母大写，不使用下划线
     * 下划线命名为，单词以小写形式，单词之间用下划线分隔
     *
     * @param string $name 驼峰式名称
     * @return string

    function camelNameToUnderlineName($name)
    {
    $nameArr = [];
    for ($i = 0; $i < strlen($name); $i++) {
    $letter = $name[$i];
    if (self::getLetterCase($letter) === self::LETTER_UPPER_CASE) {
    $letter = strtolower($letter);
    // 如果不是第一个大写字母，则在前面加下划线
    if ($i !== 0) {
    $letter = '_' . $letter;
    }
    }
    $nameArr[] = $letter;
    }

    return implode("", $nameArr);
    }  */

    /**
     * 数组转xml
     * @param array $arr 源数组
     */
    function arrayToXml($arr){
        $xml = "<xml>";
        foreach ($arr as $key=>$val){
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * xml转数组
     * @param xml $xml 源xml
     */
    function xmlToArray($xml){
        $arrayData = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $arrayData;
    }

    /**
     * 字符串中截取数字
     * @param string $str 待截取的字符串
     * @return string
     */
    function get_number($str)
    {
        $len    = strlen($str);
        $result = '';
        for ($i = 0; $i < $len; $i++) {
            if (is_numeric($str[$i])) {
                $result .= $str[$i];
            }
        }
        return $result;
    }