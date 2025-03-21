<?php
namespace lib\logging;

class LoggerFactory {

    private $loggers = [];

    /**
     * LoggerFactory 构造函数，可以接收多个日志实例
     *
     * @param mixed ...$loggers
     */
    public function __construct(...$loggers) {
        // 遍历所有传入的日志实例并将其保存
        foreach ($loggers as $logger) {
            if ($logger instanceof LoggerInterface) {
                $this->loggers[] = $logger;
            } else {
                throw new \InvalidArgumentException('Invalid logger instance');
            }
        }
    }

    /**
     * 根据配置创建合适的日志实例
     *
     * @param string $logMode 配置的日志模式，决定使用哪个日志实例
     * @return LoggerInterface 返回日志实例
     * @throws \Exception
     */
    public function create(string $logMode): LoggerInterface
    {
        // 根据日志模式选择合适的日志实例
        foreach ($this->loggers as $logger) {
            if ($logger->supports($logMode)) {
                return $logger;
            }
        }
        throw new \Exception("No suitable logger found for the mode: {$logMode}");
    }
}
