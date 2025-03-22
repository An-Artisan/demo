<?php

namespace app\Http\Controllers\Order;


use app\Http\Controllers\BaseController;
use Template;

class ChartController extends BaseController
{
    // 显示K线图页面
    public function chart($f3)
    {
//        $f3->set('content', 'welcome.htm');
        $f3->set('klineData', [
            ['time' => '2025-03-22 06:00', 'open' => 30000, 'high' => 30100, 'low' => 29900, 'close' => 30050],
            ['time' => '2025-03-22 06:15', 'open' => 30050, 'high' => 30200, 'low' => 30000, 'close' => 30150],
            ['time' => '2025-03-22 06:30', 'open' => 30150, 'high' => 30300, 'low' => 30100, 'close' => 30200],
        ]);
        echo Template::instance()->render('chart.html');
    }


    // 提供K线图数据（JSON格式）
    public function getChartData($f3)
    {
        $chartModel = new ChartModel();
//        $data = $chartModel->getKLineData();
        //TODO 暂时使用假数据
        $data = [
            [
                'timestamp' => 1672502400000, // 时间戳
                'open' => 100.0, // 开盘价
                'high' => 110.0, // 最高价
                'low' => 95.0, // 最低价
                'close' => 105.0 // 收盘价
            ],
            [
                'timestamp' => 1672588800000,
                'open' => 105.0,
                'high' => 115.0,
                'low' => 100.0,
                'close' => 110.0
            ]
        ];
        header('Content-Type: application/json');
        $this->success($data);
    }

//根据币种信息获取市场挂单列表接口
    public function getOrderBook(Base $f3)
    {
        // 获取请求参数
        $currencyPair = $f3->get('GET.currency_pair') ?? 'BTC_USDT';
        $limit = $f3->get('GET.limit') ?? 10;

        // 构建 API URL
//        $url = "https://api.gateio.ws/api/v4/spot/order_book?currency_pair={$currencyPair}&limit={$limit}";

        //ToDo  发送请求并获取数据
//        $data = $this->curlService->get($url);
        $data = [];
        // 检查数据是否获取成功
        if ($data === false) {
            $f3->error(500, "Failed to fetch data from Gate.io API");
            return;
        }

        // 返回 JSON 格式的市场挂单列表
        header('Content-Type: application/json');
        echo json_encode($data);
    }

}
