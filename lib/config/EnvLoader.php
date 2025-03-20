<?php

namespace lib\config;

class EnvLoader
{
    /**
     * 解析 `.env` 文件并加载到 `$_ENV` 和 `putenv()`
     *
     * @param string $filePath `.env` 文件路径
     */
    public static function load(string $filePath)
    {
        if (!file_exists($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // 跳过注释
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // 解析键值对
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // 去掉引号（如果有）
            if (preg_match('/^"(.*)"$/', $value, $matches) || preg_match("/^'(.*)'$/", $value, $matches)) {
                $value = $matches[1];
            }

            // 设置环境变量
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }

    /**
     * 获取 `.env` 变量
     *
     * @param string $key 变量名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return $_ENV[$key] ?? getenv($key) ?? $default;
    }
}
