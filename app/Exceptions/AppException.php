<?php

namespace app\Exceptions;

class AppException extends \Exception {
    // 自定义错误码
    protected $errorCode;

    // 自定义错误消息
    protected $errorMessage;

    // 可选的构造函数，接收错误码和错误消息
    public function __construct($message = "", $code = 0, \Exception $previous = null) {
        $this->errorMessage = $message;
        $this->errorCode = $code;
        parent::__construct($message, $code, $previous);
    }

    // 获取自定义错误消息
    public function getErrorMessage() {
        return $this->errorMessage;
    }

    // 获取自定义错误码
    public function getErrorCode() {
        return $this->errorCode;
    }
}
