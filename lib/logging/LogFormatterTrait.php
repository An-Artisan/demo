<?php

namespace lib\logging;

trait LogFormatterTrait {
    /**
     * 处理日志内容格式
     * 兼容 PHP 7.1，不使用 mixed
     *
     * @param mixed $message
     * @return string
     */
    private function formatMessage($message): string
    {
        if (is_string($message)) {
            return $message;
        }

        if (is_array($message) || is_object($message)) {
            // 尝试 JSON 编码
            $jsonMessage = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            if ($jsonMessage === false) {
                // JSON 转换失败，fallback 到 print_r
                return print_r($message, true);
            }
            return $jsonMessage;
        }

        // 其他类型直接转换字符串
        return strval($message);
    }
}
