<?php

namespace lib\logging;

class BothLogger implements LoggerInterface {

    protected $consoleLogger;
    protected $fileLogger;

    public function __construct(ConsoleLogger $consoleLogger, FileLogger $fileLogger) {
        $this->consoleLogger = $consoleLogger;
        $this->fileLogger = $fileLogger;
    }

    public function write($message, string $level) {
        // 同时输出到控制台和文件
        $this->consoleLogger->write($message, $level);
        $this->fileLogger->write($message, $level);
    }

    /**
     * 支持日志模式：'console' 或 'file'
     *
     * @param string $logMode 日志模式
     * @return bool 是否支持该日志模式
     */
    public function supports(string $logMode): bool
    {
        return $logMode === 'both';  // 仅支持控制台模式
    }
}
