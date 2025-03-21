<?php

namespace app\Http\Middleware;

use app\Http\Traits\JsonResponseTrait;

class ThrottleMiddleware extends Middleware {
    use JsonResponseTrait;
    public function handle($f3) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = "throttle_{$ip}";
        $cacheDir = __DIR__ . "/../../../storage/cache/";
        $cacheFile = $cacheDir . "{$key}.json";

        // 确保缓存目录存在
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        // 读取现有的缓存数据
        $data = file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : ['count' => 0, 'timestamp' => time()];

        // 如果时间间隔超过 60 秒，重置计数
        if ($data['timestamp'] + 60 < time()) {
            $data['count'] = 0;
            $data['timestamp'] = time();
        }

        // 递增请求计数
        $data['count']++;

        if ($data['count'] > 3) { // 限制每分钟 3 次请求
            $f3->get('log')->write('请求过于频繁，请稍后再试', 'waring');
            $this->error(429,"请求过于频繁，请稍后再试",[]);
        }
        // 保存新的计数数据
        file_put_contents($cacheFile, json_encode($data, JSON_UNESCAPED_UNICODE));

    }
}
