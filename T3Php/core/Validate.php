<?php
/**
 * 验证器基类
 * 新增一个验证方法只需要加一个验证函数和在$this->setError()中加一个错误提示消息即可。
 */

namespace T3Php\core;

use app\common\ErrorCode;

class Validate
{
    /**
     * @var array 验证错误信息
     */
    protected $error = [];
    /**
     * @var array 为具体控制器定义的验证器类对象
     */
    protected        $customValidateObject;
    protected static $selfObject = null;

    /**
     * 构造方法
     */
    public function selfObj()
    {
        if (!self::$selfObject) {
            self::$selfObject = new self;
        }
        return self::$selfObject;
    }

    /**
     * 获取验证规则
     * @param string $validateClass 验证器类
     */
    public function setRule($validateClass)
    {
        $classFile = ROOT . '/application/modules/' . T3::app()->module . '/validate/' . $validateClass . '.php';
        require_once $classFile;
        $class                      = 'app\\' . str_replace('/', '\\', T3::app()->module . '\validate\\' . $validateClass);
        $this->customValidateObject = new $class;
    }

    /**
     * 执行数据验证
     * @param array $data 要验证的数据
     */
    public function check($data)
    {
        $rules = $this->customValidateObject->rule;
        $this->selfObj();
        // 先将所有要验证的字段遍历验证一遍
        foreach ($data as $param => $value) {
            if (!isset($rules[$param])) {
                $this->error[$param] = '不存在的验证字段: ' . $param;
            } else {
                // 拿到该字段的具体规则
                $rule = $rules[$param];
                if (is_array($rule)) {
                    $ruleArray = $rule;
                } else {
                    $ruleArray = explode('|', $rule);
                }
                foreach ($ruleArray as $k => &$r) {
                    $r = trim($r);
                }
                // 走框架内置验证方法
                foreach ($ruleArray as $ruleFunc) {
                    // 检查内置验证方法是否存在
                    if ($ruleFunc == 'required') {
                        continue;  // 放行required方法
                    }
                    if (!$this->checkFuncExists($ruleFunc)) {
                        $this->error[$param] = '不存在的验证规则: ' . $ruleFunc;
                        continue;
                    }

                    // 进行验证
                    // max:25 这种
                    if (strpos($ruleFunc, ':')) {
                        list($T3Rule, $ruleValue) = explode(':', $ruleFunc);
                        $T3Rule    = trim($T3Rule);
                        $ruleValue = trim($ruleValue);

                        // 走自定义验证规则
                        if ($T3Rule == 'T3Php') {
                            if (!method_exists($this->customValidateObject, $ruleValue)) {
                                // TODO 不存在的验证方法可能有多个，需要用 $this->setError() 设置错误信息
                                $this->error[$param] = '不存在的自定义验证方法: ' . $ruleValue . '()';
                            } else {
                                $result = $this->customValidateObject->{$ruleValue}($value);
                                if ($result != true || $result != 1) {
                                    $this->error[$param] = $result;
                                }
                            }
                            continue;
                        }

                        // 框架内置验证规则
                        if (!$this->{$T3Rule}($value, $ruleValue)) {
                            $this->setError($param, $T3Rule, $ruleValue);
                        }
                    } else {
                        if (!$this->{$ruleFunc}($value)) {
                            $this->setError($param, $ruleFunc);
                        }
                    }
                }
            }
        }
        // 再将所有规则遍历一遍查找是否少了必传字段
        foreach ($rules as $key => $item) {
            if (is_array($item)) {
                $ruleArray = $item;
            } else {
                $ruleArray = explode('|', $item);
            }
            foreach ($ruleArray as $ruleItem) {
                if ($ruleItem == 'required' && !isset($data[$key])) {
                    $this->setError($key, 'required');
                }
            }
        }
        if (empty($this->error)) {
            return true;
        }
        return false;
    }

    /**
<<<<<<< HEAD
     * 简单参数验证
     * @param array $params
     * @return array
     */
    public function checks($params)
    {
        // 获取验证规则
        $rule = $this->customValidateObject->rule;
        // 验证规则不能为空
        if (empty($rule)) {
            $this->setErrors('', 'empty rule');
            return [$this->error, '数据验证失败', ErrorCode::PARAM_ERROR];
        }
        // 验证规则为数组格式
        if (!is_array($rule)) {
            $this->setErrors('', 'rule array');
            return [$this->error, '数据验证失败', ErrorCode::PARAM_ERROR];
        }
        // 验证
        foreach ($rule as $key => $value) {
            // 获取验证条件 转为数组格式
            if (is_array($value)) {
                $ruleArr = $value;
            } else {
                $ruleArr = explode('|', $value);
            }
            // 依据验证条件逐条验证
            foreach ($ruleArr as $k => $v) {
                if (is_int($k)) {
                    $checkRule = $v;
                } else {
                    $checkRule = $k;
                }
                // 必填项验证
                if ($checkRule == 'require' && !isset($params[$key])) {
                    $this->setErrors($key, 'require');
                    return [$this->error, '数据验证失败', ErrorCode::PARAM_ERROR];
                }
                if (isset($params[$key])) {
                    switch ($checkRule) {
                        case 'require' :
                            // 必填项已验证 跳出
                            break;
                        case 'integer' :
                            if (!is_numeric($params[$key])) {
                                $this->setErrors($key, 'integer');
                                return [$this->error, '数据验证失败', ErrorCode::PARAM_ERROR];
                            }
                            break;
                        case 'max' :
                            if (!self::max($params[$key], $v)) {
                                $this->setErrors($key, 'max');
                                return [$this->error, '数据验证失败', ErrorCode::PARAM_ERROR];
                            }
                            break;
                        case 'min' :
                            if (!self::min($params[$key], $v)) {
                                $this->setErrors($key, 'min');
                                return [$this->error, '数据验证失败', ErrorCode::PARAM_ERROR];
                            }
                            break;
                        case 'length' :
                            if (!self::length($params[$key], $v)) {
                                $this->setErrors($key, 'regex');
                                return [$this->error, '数据验证失败', ErrorCode::PARAM_ERROR];
                            }
                            break;
                        case 'between' :
                            if (!self::between($params[$key], $v)) {
                                $this->setErrors($key, 'between');
                                return [$this->error, '数据验证失败', ErrorCode::PARAM_ERROR];
                            }
                            break;
                        case 'reregexgex' :
                            if (!preg_match($params[$key], $value)) {
                                $this->setErrors($key, 'regex');
                                return [$this->error, '数据验证失败', ErrorCode::PARAM_ERROR];
                            }
                            break;
                        default :
                            $this->setErrors($key, 'null');
                            return [$this->error, '数据验证失败', ErrorCode::PARAM_ERROR];
                    }
                }
            }
        }
        return [null, 'success', ErrorCode::SUCCESS];
    }

    /**
=======
>>>>>>> bf86d93fadb7f4f4103e3ed9846de9233afc9a5c
     * 检测验证函数是否存在
     * @param string $func 目标验证函数
     */
    protected function checkFuncExists($func)
    {
        // max:25 这种
        if (strpos($func, ':')) {
            list($func2, $funcValue) = explode(':', $func);
            if (!method_exists(self::$selfObject, $func2)) {
                return false;
            }
        } else {
            if (!method_exists(self::$selfObject, $func)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取已验证的错误信息
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 验证字符串最大长度
     * @param mixed $value 要验证的字段的值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function max($value, $rule)
    {
        $length = mb_strlen((string)$value);
        return $length <= $rule;
    }

    /**
     * 参数验证(最小长度)
     * @param mixed $value 要验证的字段的值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function min($value, $rule)
    {
        $length = mb_strlen((string)$value);
        return $length >= $rule;
    }

    /**
     * 参数验证(长度)
     * @param  mixed  $value 要验证的字段的值
     * @param  mixed  $rule  验证规则
     * @return bool
     */
    public function length($value, $rule)
    {
        $length = mb_strlen((string) $value);
        return $length == $rule;
    }

    /**
     * 参数验证(区间值)
     * @param  mixed  $value 要验证的字段的值
     * @param  mixed  $rule  验证规则 必须是个区间数组 如取值1-3：[1,3]
     * @return bool
     */
    public function between($value, $rule)
    {
        if (!is_array($rule) || count($rule) != 2) {
            return false;
        }

        list($min, $max) = $rule;

        return $value >= $min && $value <= $max;
    }


    /**
     * 验证是否整形
     * @param mixed $value 要验证的字段的值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function int($value)
    {
        return is_int($value);
    }

    /**
     * 验证是否数字型或者数字字符串
     * @param mixed $value 要验证的字段的值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function number($value)
    {
        return is_numeric($value);
    }

    /**
     * 错误提示信息
     * @param mixed $field 被验证的字段
     * @param string $ruleFunc 验证规则,也就是具体的验证函数名
     * @param string $ruleValue 针对 max:20这种验证规则
     */
    public function setError($field, $ruleFunc, $ruleValue = null)
    {
        $message['max']      = '最大长度不能超过 ' . $ruleValue;
        $message['required'] = $field . ' 不能为空';
        $message['int']      = $field . ' 必须为整形';
        $message['number']   = $field . ' 必须为数字形';

        // 填冲错误信息
        if (isset($this->error[$field])) {
            $temp                           = $this->error[$field];
            $this->error[$field]            = [];
            $this->error[$field][$ruleFunc] = $this->getCustomMessage($message[$ruleFunc], $field, $ruleFunc);
            if (is_array($temp)) {
                array_merge($this->error[$field], $temp);
            } else {
                $this->error[$field][$field] = $temp;
            }
        } else {
            $this->error[$field] = $this->getCustomMessage($message[$ruleFunc], $field, $ruleFunc);
        }
    }

    /* 使用自定义错误消息
     * @param string $t3msg 框架内置错误提示消息
     */
    public function getCustomMessage($t3msg, $field, $ruleFunc)
    {
        if (isset($this->customValidateObject->message)) {
            $messages   = $this->customValidateObject->message;
            $messageKey = $field . '.' . $ruleFunc;
            if (isset($messages[$messageKey])) {
                return $messages[$messageKey];
            }
        }
        return $t3msg;
    }
}
