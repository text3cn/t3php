<?php
/**
 * 控制器基类
 */
namespace T3Php\core;


use app\common\ErrorCode;

class HttpController extends Controller
{
    public $connection;
    public $request;
    public $params;
    public $method;


    public function __construct()
    {
        parent::__construct();
        $headers = $this->getAllHeaders();
        $params                 = array_merge($_GET, $_POST);
        if (isset(T3::app()->workermanConnection)){
            $get                = T3::app()->workermanRequest->get();
            $post               = T3::app()->workermanRequest->post();
            $header             = T3::app()->workermanRequest->header();
            $params             = array_merge($params, $get, $post, $header);
        }
        $this->params           = self::filterParams($params);
        $this->params['get']    = $_GET;
        $this->params['post']   = $_POST;
        $this->params['header'] = $headers;
    }


    /**
     *
     *接收头信息
     **/
    function getAllHeaders()
    {
        $headers = [];
        if(isset($_SERVER)) {
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        }
        return $headers;
    }


    /**
     * 过滤请求参数(空格 特殊字符)
     * @param $params
     */
    public function filterParams($params)
    {
        if (!empty($params) && is_array($params)) {
            foreach ($params as &$item) {
                $item = trim($item);
                $item = htmlspecialchars($item);
                $item = strip_tags($item);
            }
        }
        return $params;
    }


    /**
     * 参数获取
     * @param string $param   要接受的参数
     * @param string $default 默认值
     */
    public function param($param, $default = null){
        if(isset($this->params[$param]) && !empty($this->params[$param])){
            return htmlspecialchars(addslashes($this->params[$param]));
        }
        return $default;
    }

    /**
     * 根据请求参数筛选要保存的数组
     * @param array $params 请求参数列表
     * @return $data
     */
    public function filterParam($params){
        $data = [];
        foreach($params as $item){
            if(isset($this->params[$item])){
                $data[$item] = addslashes($this->params[$item]);
            }
        }
        return $data;
    }

    /**
     * 返回响应信息
     * @param mixed   $data 响应数据
     * @param string  $message 用户提示消息
     * @param int     $errorCode 错误代码
     * @param array   $header 发送header头信息，例如：['X-Header-One' => 'Header Value']
     * @param int     $httpCode http状态码
     */
    public function response($data, $message = '', $errorCode = 0, $header = [], $httpCode = 200){
        T3::app()->response($data, $message, $errorCode, $header, $httpCode);
    }

    /**
     * 渲染浏览器页面
     * @param string  $html html代码
     * @param int     $data 分配给视图文件用的变量
     * @param int     $httpCode http状态码
     * @param array   $header 发送header头信息，例如：['X-Header-One' => 'Header Value']，Workerman会自动追加'Content-Type' => 'text/html'
     */
    public function renderView($file = '', $data =[], $httpCode = 200, $header = []){
        $filePath = ROOT . DIRECTORY_SEPARATOR  . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR .  T3::app()->module . 'view' . DIRECTORY_SEPARATOR . $file;
        if(!is_file($filePath)){
            T3::app()->view('视图文件 ' . $file . ' 不存在', 500, $header);
        }
        T3::app()->view(file_get_contents($filePath), $httpCode, $header);
    }



    /**
     * 获取/设置cookie的值
     * @param string $key cookie键
     * @param mixed $value cookie值
     */
    public function cookie($key, $value = null){
        T3::app()->cookie($key, $value);
    }

    /**
     * 获取/设置session的值
     * @param string $key session key
     * @param mixed $value session value
     */
    public function session($key, $value = null){
        T3::app()->session($key, $value);
    }
}
