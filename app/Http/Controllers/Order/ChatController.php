<?php

namespace app\Http\Controllers\Order;


class ChartController {
    // 显示K线图页面
    public function showChart($f3) {
        echo Template::instance()->render('app/views/chart.html');
    }

    // 提供K线图数据（JSON格式）
    public function getChartData($f3) {
        $chartModel = new ChartModel();
//        $data = $chartModel->getKLineData();
        //TODO 暂时使用假数据
        $data =  [
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
        echo json_encode($data);
    }
}
?>
