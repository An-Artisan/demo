document.addEventListener('DOMContentLoaded', function () {
    // 获取K线图数据
    fetch('/chart/data')
        .then(response => response.json())
        .then(data => {
            // 处理数据
            const chartData = data.map(item => [
                item.timestamp,
                item.open,
                item.close,
                item.low,
                item.high
            ]);

            // 初始化ECharts
            const chart = echarts.init(document.getElementById('chart'));
            const option = {
                title: {
                    text: '数字货币K线图'
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross'
                    }
                },
                xAxis: {
                    type: 'time'
                },
                yAxis: {
                    scale: true
                },
                series: [
                    {
                        type: 'candlestick',
                        data: chartData
                    }
                ]
            };
            chart.setOption(option);
        })
        .catch(error => console.error('Error fetching chart data:', error));
});
