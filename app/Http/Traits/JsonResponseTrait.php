<?php
/**
 * JSON 响应 Trait，为控制器和中间件提供统一的 JSON 输出方法。
 */

namespace app\Http\Traits;

trait JsonResponseTrait {

    /**
     * 返回标准的成功 JSON 响应
     *
     * @param array $data    响应的数据内容，默认为空数组。
     * @param string $message 响应的消息，默认为空字符串。
     */
    public function success(array $data = [], string $message = "success") {
        $response = [
            "code"    => 200,       // 成功状态码固定为 200
            "data"    => $data,     // 数据内容，可以是数组或对象
            "message" => $message   // 消息内容，一般为操作说明或空
        ];
        $this->sendJsonResponse(200, $response);
    }

    /**
     * 返回标准的错误 JSON 响应
     *
     * @param int $code    错误代码（业务 状态码），例如 10001, 10002, 10003 等。
     * @param string $message 错误消息，描述错误原因。
     * @param array $data    可选的数据内容，默认为空数组。
     */
    public function error(int $code, string $message, array $data = []) {
        $response = [
            "code"    => $code,     // 错误状态码，通常对应 HTTP 状态码
            "data"    => $data,     // 数据内容，可用于传递错误的相关数据
            "message" => $message   // 错误消息，描述失败原因
        ];
        $this->sendJsonResponse(400, $response);
    }

    /**
     * 发送 JSON 响应，避免代码重复
     *
     * @param int $httpCode HTTP 状态码
     * @param array $response 响应内容
     */
    private function sendJsonResponse(int $httpCode, array $response) {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
