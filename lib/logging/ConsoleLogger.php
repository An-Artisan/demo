<?php
namespace lib\logging;

class ConsoleLogger implements LoggerInterface {


    public function write(string $message, string $level) {
        // 检测是否为命令行环境
        if (php_sapi_name() === 'cli') {
            // 输出到命令行控制台
            echo "[{$level}] " . $message . PHP_EOL;
        }
    }

    public function supports(string $logMode): bool
    {
        return $logMode === 'console';  // 仅支持控制台模式
    }
}
