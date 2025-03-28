<?php

namespace lib\logging;

interface LoggerInterface {
    /**
     * 写入日志
     *
     * @param mixed $message 日志消息
     * @param string $level 日志级别
     */
    public function write( $message, string $level);

    /**
     * 检查当前日志实例是否支持给定的日志模式
     *
     * @param string $logMode
     * @return bool
     */
    public function supports(string $logMode): bool;
}
