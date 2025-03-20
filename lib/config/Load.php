<?php

namespace lib\config;

class Load
{
    protected static $config = [];

    /**
     * 加载配置
     */
    public static function load()
    {
        if (empty(self::$config)) {
            // 加载 .env
            EnvLoader::load(__DIR__ . '/../.env');

            // 加载 config.php
            self::$config = require __DIR__ . '/../../config/config.php';
        }
    }

    /**
     * 获取配置项
     * @param string $key 配置键，如 'app.name'
     * @param mixed $default 默认值（可选）
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        self::load();

        $keys = explode('.', $key);
        $config = self::$config;

        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                return $default;
            }
            $config = $config[$k];
        }

        return $config;
    }
}
