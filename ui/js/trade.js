// IIFE模块封装
(() => {
    // 常量定义
    const SYMBOL = 'BTC_USDT';
    const INTERVALS = ['1m', '5m', '15m', '1h', '4h', '1d'];
    const API_URL = '/api';


    // 模块变量
    let klineChart;
    let activeInterval = '1h';
    let orderBookTimeout;

    // DOM缓存
    const dom = {
        klineCanvas: document.getElementById('klineChart'),
        bidsList: document.getElementById('bidsList'),
        asksList: document.getElementById('asksList'),
        orderLoader: document.getElementById('orderLoader'),
        lastPrice: document.getElementById('lastPrice'),
        priceChange: document.getElementById('priceChange'),
        volume: document.getElementById('volume'),
        high: document.getElementById('high'),
        low: document.getElementById('low')
    };

    // 初始化模块
    function init() {
        initChart();
        loadMarketData();
        startOrderBookUpdate();
        initEventListeners();
    }

    // 初始化图表
    function initChart() {
        klineChart = new Chart(dom.klineCanvas, {
            type: 'candlestick',
            data: {
                labels: [],
                datasets: [{
                    label: 'K线图',
                    data: [],
                    borderColor: '#2ecc71',
                    backgroundColor: '#2ecc7122',
                    increasing: { borderColor: '#2ecc71', backgroundColor: '#2ecc7122' },
                    decreasing: { borderColor: '#e74c3c', backgroundColor: '#e74c3c22' }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { type: 'time' },
                    y: { title: { display: true, text: '价格' } }
                }
            }
        });
    }

    // 加载市场数据
    async function loadMarketData() {
        try {
            const [kline, orderBook, ticker] = await Promise.all([
                fetch(`${API_URL}/kline?symbol=${SYMBOL}&interval=${activeInterval}`),
                fetch(`${API_URL}/order_book?symbol=${SYMBOL}`),
                fetch(`${API_URL}/ticker?symbol=${SYMBOL}`)
            ]);

            updateKline(await kline.json());
            updateOrderBook(await orderBook.json());
            updateTicker(await ticker.json());
        } catch (error) {
            console.error('数据加载失败:', error);
            showError('网络连接失败，请检查网络');
        }
    }

    // 更新K线数据（优化渲染）
    // function updateKline(data) {
    //     const chartData = data.map(([time, open, high, low, close]) => ({
    //         x: new Date(time * 1000),
    //         open, high, low, close
    //     }));
    //
    //     klineChart.data.labels = chartData.map(item => item.x);
    //     klineChart.data.datasets[0].data = chartData;
    //     klineChart.update({ duration: 0 }); // 无动画更新
    // }

    function updateKline(data) {
        const chartData = data.map(([time, open, high, low, close]) => ({
            x: new Date(time * 1000), // 转换为Date对象
            open: parseFloat(open),
            high: parseFloat(high),
            low: parseFloat(low),
            close: parseFloat(close)
        }));

        klineChart.data.datasets[0].data = chartData;
        klineChart.update();
    }

    // 订单簿更新（防抖处理）
    const updateOrderBookDebounced = debounce(async () => {
        try {
            const data = await (await fetch(`${API_URL}/order_book?symbol=${SYMBOL}`)).json();
            updateOrderBookUI(data);
        } catch (error) {
            console.error('订单簿更新失败:', error);
        }
    }, 1000);

    function startOrderBookUpdate() {
        orderBookTimeout = setInterval(updateOrderBookDebounced, 3000);
    }

    // 表单处理
    function placeOrder(event) {
        event.preventDefault();
        const amount = parseFloat(dom.amount.value);
        const price = parseFloat(dom.price.value);

        if (!validateOrder(amount, price)) return;

        dom.orderLoader.style.display = 'inline-block';
        // 实际下单逻辑（需替换为真实API）
        setTimeout(() => {
            dom.orderLoader.style.display = 'none';
            alert('订单已提交（模拟操作）');
        }, 1000);
    }

    // 工具函数
    function debounce(func, delay) {
        let timeoutId;
        return (...args) => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }

    function validateOrder(amount, price) {
        if (isNaN(amount) || amount <= 0) {
            showError('数量必须为正数值');
            return false;
        }
        if (isNaN(price) || price <= 0) {
            showError('价格必须为正数值');
            return false;
        }
        return true;
    }

    function showError(message) {
        alert(`错误: ${message}`);
    }

    // 初始化事件
    function initEventListeners() {
        document.querySelector('.theme-toggle').addEventListener('click', Theme.toggle);
        document.querySelector('.interval-selector').addEventListener('click', () => {
            INTERVALS.forEach(interval => {
                // 这里可以添加下拉菜单的UI逻辑
            });
        });
    }

    // 主题模块
    const Theme = {
        toggle() {
            const body = document.body;
            const currentTheme = body.dataset.theme;
            body.dataset.theme = currentTheme === 'light' ? 'dark' : 'light';
            localStorage.setItem('theme', body.dataset.theme);
        },
        init() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) document.body.dataset.theme = savedTheme;
        }
    };

    // 启动应用
    Theme.init();
    init();
})();