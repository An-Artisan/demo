<?php

namespace lib\logging;

class ConsoleLogger implements LoggerInterface
{
    protected $useColors;
    use LogFormatterTrait;

    // 复用格式化 Trait

    public function __construct($useColors = true)
    {
        $this->useColors = $useColors;
    }

    public function write( $message, string $level)
    {
        // 如果是 Web 环境（非 CLI），不输出日志
        if (!$this->isCli()) {
            return;
        }


        $formattedMessage = $this->formatMessage($message);
        $logMessage = "[{$level}] {$formattedMessage}" . PHP_EOL;

        // 判断是否是交互式终端 (不包括 crontab)
        if ($this->isInteractiveShell()) {
            // 交互模式下使用颜色
            $color = $this->getColorForLevel($level);
            $logMessage = "\033[{$color}m{$logMessage}\033[0m";
        }

        // 直接输出到终端 (CLI 或 Cron)
        echo $logMessage;
    }


    /**
     * 判断当前是否是 CLI（命令行模式）
     */
    private function isCli(): bool
    {
        return PHP_SAPI === 'cli';
    }


    /**
     * 判断当前是否是交互式终端
     */
    private function isInteractiveShell(): bool
    {
        return function_exists('posix_isatty') && posix_isatty(STDOUT);
    }

    private function getColorForLevel($level): string
    {
        switch (strtolower($level)) {
            case 'error':
                return '0;31';  // 红色
            case 'warning':
                return '1;33'; // 黄色
            case 'info':
                return '0;32';  // 绿色
            case 'debug':
                return '0;36';  // 青色
            default:
                return '0'; // 默认无颜色
        }
    }

    public function supports(string $logMode): bool
    {
        return $logMode === 'console';
    }
}
