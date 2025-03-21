<?php

namespace lib\logging;

class FileLogger implements LoggerInterface {

    protected $logFile;

    public function __construct() {
        $logDir = __DIR__ . '/../../storage/logs';
        $this->logFile = $logDir . '/app.log';

        // 如果日志目录不存在，则创建
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true); // 递归创建目录，权限可按需调整
        }

        // 如果日志文件不存在，则创建一个空文件
        if (!file_exists($this->logFile)) {
            file_put_contents($this->logFile, '');
        }

        // 检查文件是否可写
        if (!is_writable($this->logFile)) {
            throw new \RuntimeException("Log file is not writable: {$this->logFile}");
        }
    }
    public function write(string $message, string $level) {
        $logMessage = "[" . date('Y-m-d H:i:s') . "] [{$level}] {$message}\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);  // 写入文件
    }

    public function supports($logMode): bool
    {
        return $logMode === 'file';
    }
}
