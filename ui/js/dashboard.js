const useAjax = false;
let currentTab = 'current';
let currentCoin = 'BTC';
let pollingIntervalId = null; // 定时器 ID

function fetchCoinList() {
    axios.get('http://localhost:8888/coins/get-currency-list').then(res => {
        if (res.data.code === 200) {
            let list = res.data.data;
            const priority = ['BTC_USDT', 'ETH_USDT'];
            const sortedList = [
                ...list.filter(item => priority.includes(item.id)).sort((a, b) => priority.indexOf(a.id) - priority.indexOf(b.id)),
                ...list.filter(item => !priority.includes(item.id))
            ];
            const select = document.getElementById('coinPairSelect');
            select.innerHTML = '';
            sortedList.forEach(item => {
                const option = document.createElement('option');
                option.value = item.base;
                option.text = `${item.base}/${item.quote}`;
                select.appendChild(option);
            });
            currentCoin = sortedList[0].base;
            updatePage(currentCoin);
        }
    });
}

function fetchMarketInfo(base) {
    axios.get('http://localhost:8888/coins/get-currency-info?currency_pair=' + `${base}_USDT`).then(res => {
        if (res.data.code === 200) {
            const info = res.data.data.find(item => item.currency_pair === `${base}_USDT`);
            if (info) {
                const change = parseFloat(info.change_percentage);
                const infoBar = document.getElementById('marketInfo');
                infoBar.innerText = `当前币种: ${info.currency_pair.replace('_', '/')}, 价格: ${info.last}, 涨幅: ${change.toFixed(2)}%`;
                infoBar.className = 'info-bar ' + (change >= 0 ? 'up' : 'down');
            }
        }
    });
}
function togglePriceField() {
    const orderType = document.getElementById('orderType').value;
    const priceInput = document.getElementById('priceInput');

    if (orderType === 'market') {
        priceInput.style.display = 'none';
    } else {
        priceInput.style.display = 'block';
    }
}
function fetchKlineData(base) {
    axios.get(`http://localhost:8888/coins/get-currency-kline?currency_pair=${base}_USDT&interval=1h&limit=1000`).then(res => {
        if (res.data.code === 200) {
            const klineRaw = res.data.data;
            const klineFormatted = klineRaw.map(item => {
                const time = new Date(item[0] * 1000).toLocaleString('zh-CN', {
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                }).replace(/\//g, '-');
                return [time, parseFloat(item[4]), parseFloat(item[2]), parseFloat(item[3]), parseFloat(item[5])];
            });
            renderKline(klineFormatted);
        }
    });
}

function fetchOrderBook(base) {
    axios.get(`http://localhost:8888/coins/get-currency-depth?currency_pair=${base}_USDT`)
        .then(res => {
            if (res.data.code === 200) {
                const asks = res.data.data.asks.slice(0, 10).reverse(); // 卖盘倒序（卖1在底部）
                const bids = res.data.data.bids.slice(0, 10);           // 买盘正序（买1在顶部）

                let html = '<tr><th>卖出价</th><th>卖出量</th><th>买入价</th><th>买入量</th></tr>';
                for (let i = 0; i < 10; i++) {
                    const ask = asks[i] || ["", ""];
                    const bid = bids[i] || ["", ""];
                    html += `<tr>
                        <td>${ask[0]}</td><td>${ask[1]}</td>
                        <td>${bid[0]}</td><td>${bid[1]}</td>
                    </tr>`;
                }
                document.getElementById('orderBookTable').innerHTML = html;
            }
        });
}

function fetchOrderBookWithLocal(base) {
    axios.get(`http://localhost:8888/coins/get-currency-depth-local?pair_id=${base}_USDT`)
        .then(res => {
            if (res.data.code === 200) {
                const asks = res.data.data.asks || [];
                const bids = res.data.data.bids || [];

                // 判断是否无数据
                if (asks.length === 0 && bids.length === 0) {
                    document.getElementById('orderBookTableLocal').innerHTML = `
                        <tr><th colspan="4" style="text-align:center;color:#999;">暂无数据</th></tr>
                    `;
                    return;
                }

                const asksShow = asks.slice(0, 10).reverse(); // 卖盘倒序
                const bidsShow = bids.slice(0, 10);           // 买盘正序

                let html = '<tr><th>卖出价</th><th>卖出量</th><th>买入价</th><th>买入量</th></tr>';

                const maxRows = Math.max(asksShow.length, bidsShow.length, 10);
                for (let i = 0; i < maxRows; i++) {
                    const ask = asksShow[i] || [];
                    const bid = bidsShow[i] || [];

                    const askPrice = ask[0] || '<span style="color:#999"></span>';
                    const askAmount = ask[1] || '';
                    const bidPrice = bid[0] || '<span style="color:#999"></span>';
                    const bidAmount = bid[1] || '';

                    html += `<tr>
                        <td>${askPrice}</td><td>${askAmount}</td>
                        <td>${bidPrice}</td><td>${bidAmount}</td>
                    </tr>`;
                }

                document.getElementById('orderBookTableLocal').innerHTML = html;
            }
        });
}

function fetchLatestTrades(pairId) {
    axios.get(`http://localhost:8888/order/get-latest-trades?pair_id=${pairId}`).then(res => {
        if (res.data.code === 200) {
            const list = res.data.data.list;
            const table = document.querySelector('#tradeTable tbody');
            table.innerHTML = '';

            if (list.length === 0) {
                table.innerHTML = `<tr><td colspan="4" style="text-align:center;color:#999;">暂无数据</td></tr>`;
                return;
            }

            list.forEach(trade => {
                table.innerHTML += `<tr>
                    <td>${trade.created_at}</td>
                    <td>${trade.price}</td>
                    <td>${trade.amount}</td>
                    <td>${trade.fee}</td>
                </tr>`;
            });
        }
    });
}

function fetchCurrentOrders(base) {
    axios.get(`http://localhost:8888/order/get-current-order-list?pair_id=${base}`).then(res => {
        const tbody = document.querySelector('#currentOrdersTable tbody');
        tbody.innerHTML = '';

        const orderList = res.data.data; // 数据结构是数组了

        if (res.data.code === 200 && Array.isArray(orderList) && orderList.length > 0) {
            orderList.forEach(order => {
                const typeText = order.type === 0 ? '限价' : '市价';
                const sideText = order.side === 0 ? '买入' : '卖出';
                const statusMap = {
                    0: '未成交',
                    1: '部分成交',
                    2: '已成交',
                    3: '已取消'
                };
                const statusText = statusMap[order.status] || '未知';
                const displayPrice = order.type === 1 ? '-' : order.price;

                tbody.innerHTML += `
                <tr>
                    <td id="${order.order_id}">${order.order_id}</td>
                    <td>${order.pair_id}</td>
                    <td>${typeText}</td>
                    <td>${sideText}</td>
                    <td>${displayPrice}</td>
                    <td>${order.amount}</td>
                    <td>${order.filled_amount}</td>
                    <td onclick="cancelOrder(${order.order_id})">${statusText}</td>
                    <td>${order.created_at}</td>
                </tr>`;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;">当前暂无数据</td></tr>';
        }
    });
}
function fetchHistoryOrders(base) {
    axios.get(`http://localhost:8888/order/get-history-order-list?pair_id=${base}`).then(res => {
        const tbody = document.querySelector('#currentOrdersTable tbody');
        tbody.innerHTML = '';

        const orderList = res.data.data; // 数据结构是数组了

        if (res.data.code === 200 && Array.isArray(orderList) && orderList.length > 0) {
            orderList.forEach(order => {
                const typeText = order.type === 0 ? '限价' : '市价';
                const sideText = order.side === 0 ? '买入' : '卖出';
                const statusMap = {
                    0: '未成交',
                    1: '部分成交',
                    2: '已成交',
                    3: '已取消'
                };
                const statusText = statusMap[order.status] || '未知';
                const displayPrice = order.type === 1 ? '-' : order.price;

                tbody.innerHTML += `
                <tr>
                    <td>${order.order_id}</td>
                    <td>${order.pair_id}</td>
                    <td>${typeText}</td>
                    <td>${sideText}</td>
                    <td>${displayPrice}</td>
                    <td>${order.amount}</td>
                    <td>${order.filled_amount}</td>
                    <td>${statusText}</td>
                    <td>${order.created_at}</td>
                </tr>`;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;">暂无当前委托</td></tr>';
        }
    });
}
function fetchTradeRecords(base) {
    axios.get(`http://localhost:8888/order/get-filled-order-list?pair_id=${base}`).then(res => {
        const tbody = document.querySelector('#currentOrdersTable tbody');
        tbody.innerHTML = '';

        const orderList = res.data.data; // 数据结构是数组了

        if (res.data.code === 200 && Array.isArray(orderList) && orderList.length > 0) {
            orderList.forEach(order => {
                const typeText = order.type === 0 ? '限价' : '市价';
                const sideText = order.side === 0 ? '买入' : '卖出';
                const statusMap = {
                    0: '未成交',
                    1: '部分成交',
                    2: '已成交',
                    3: '已取消'
                };
                const statusText = statusMap[order.status] || '未知';

                tbody.innerHTML += `
                <tr>
                    <td>${order.order_id}</td>
                    <td>${order.pair_id}</td>
                    <td>${typeText}</td>
                    <td>${sideText}</td>
                    <td>${order.price}</td>
                    <td>${order.amount}</td>
                    <td>${order.filled_amount}</td>
                    <td>${statusText}</td>
                    <td>${order.created_at}</td>
                </tr>`;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;">暂无当前委托</td></tr>';
        }
    });
}
function fetchAssetData(pairId) {
    const [base, quote] = pairId.split('_'); // 拆成 BTC 和 USDT
    const tbody = document.querySelector('#assetTable tbody');
    tbody.innerHTML = ''; // 清空旧内容

    let totalAmount = 0;
    let currencySymbol = '';

    // 请求 quote 币种资产（主展示）
    axios.get(`http://localhost:8888/users/get-balance?currency=${quote}`).then(res => {
        if (res.data.code === 200) {
            const data = res.data.data;
            const total = data.total;
            const details = data.details;

            totalAmount = parseFloat(total.amount);
            currencySymbol = total.currency;

            // ✅ 分账户展示（只展示金额 > 0）
            for (let type in details) {
                const row = details[type];
                if (parseFloat(row.amount) > 0) {
                    let label = type;
                    if (type === 'finance') label = '金融余额';
                    else if (type === 'futures') label = '合约余额';
                    else if (type === 'spot') label = '现货余额';
                    tbody.innerHTML += `<tr><td>${label}</td><td>${row.amount}</td><td>${row.currency}</td></tr>`;
                }
            }

            // ✅ 现货账户详情（可用/锁定余额）
            let token = data.spot?.find(item => item.currency === quote.toUpperCase());
            const available = token ? token.available : '0';
            const locked = token ? token.locked : '0';
            tbody.innerHTML += `<tr><td>${quote} 可用余额</td><td>${available}</td><td>${quote}</td></tr>`;
            tbody.innerHTML += `<tr><td>${quote} 锁定余额</td><td>${locked}</td><td>${quote}</td></tr>`;

            // ✅ 设置总资产
            document.getElementById('totalAsset').innerText = `总资产：${totalAmount.toFixed(4)} ${currencySymbol}`;
        }
    });

    // 请求 base 币种，仅展示可用/锁定余额
    axios.get(`http://localhost:8888/users/get-balance?currency=${base}`).then(res => {
        if (res.data.code === 200) {
            const data = res.data.data;
            let token = data.spot?.find(item => item.currency === base.toUpperCase());
            const available = token ? token.available : '0';
            const locked = token ? token.locked : '0';

            tbody.innerHTML += `<tr><td>${base} 可用余额</td><td>${available}</td><td>${base}</td></tr>`;
            tbody.innerHTML += `<tr><td>${base} 锁定余额</td><td>${locked}</td><td>${base}</td></tr>`;
        }
    }).catch(err => {
        console.error('获取本地资产失败', err);
        document.getElementById('totalAsset').innerText = '资产加载失败 ❌';
    });
}


function fetchAssetDataLocal(pairId) {
    const [base, quote] = pairId.split('_'); // 拆成 BTC 和 USDT
    const tbody = document.querySelector('#assetTableLocal tbody');
    tbody.innerHTML = ''; // 清空旧内容

    let totalAmount = 0;
    let currencySymbol = '';

    // 请求 quote 币种资产（主展示）
    axios.get(`http://localhost:8888/users/get-balance-local?currency=${quote}`).then(res => {
        if (res.data.code === 200) {
            const data = res.data.data;
            const total = data.total;
            const details = data.details;

            totalAmount = parseFloat(total.amount);
            currencySymbol = total.currency;

            // ✅ 分账户展示（只展示金额 > 0）
            for (let type in details) {
                const row = details[type];
                if (parseFloat(row.amount) > 0) {
                    let label = type;
                    if (type === 'finance') label = '金融余额';
                    else if (type === 'futures') label = '合约余额';
                    else if (type === 'spot') label = '现货余额';
                    tbody.innerHTML += `<tr><td>${label}</td><td>${row.amount}</td><td>${row.currency}</td></tr>`;
                }
            }

            // ✅ 现货账户详情（可用/锁定余额）
            let token = data.spot?.find(item => item.currency === quote.toUpperCase());
            const available = token ? token.available : '0';
            const locked = token ? token.locked : '0';
            tbody.innerHTML += `<tr><td>${quote} 可用余额</td><td>${available}</td><td>${quote}</td></tr>`;
            tbody.innerHTML += `<tr><td>${quote} 锁定余额</td><td>${locked}</td><td>${quote}</td></tr>`;

            // ✅ 设置总资产
            document.getElementById('totalAssetLocal').innerText = `总资产：${totalAmount.toFixed(4)} ${currencySymbol}`;
        }
    });

    // 请求 base 币种，仅展示可用/锁定余额
    axios.get(`http://localhost:8888/users/get-balance-local?currency=${base}`).then(res => {
        if (res.data.code === 200) {
            const data = res.data.data;
            let token = data.spot?.find(item => item.currency === base.toUpperCase());
            const available = token ? token.available : '0';
            const locked = token ? token.locked : '0';

            tbody.innerHTML += `<tr><td>${base} 可用余额</td><td>${available}</td><td>${base}</td></tr>`;
            tbody.innerHTML += `<tr><td>${base} 锁定余额</td><td>${locked}</td><td>${base}</td></tr>`;
        }
    }).catch(err => {
        console.error('获取本地资产失败', err);
        document.getElementById('totalAssetLocal').innerText = '资产加载失败 ❌';
    });
}







function updatePage(coinKey) {
    currentCoin = coinKey;
    document.getElementById('amountInput').placeholder = `数量（${coinKey}）`;

    // ✅ 首次加载执行一次
    fetchMarketInfo(coinKey);
    fetchKlineData(coinKey);
    fetchOrderBook(coinKey);
    fetchOrderBookWithLocal(coinKey);
    fetchAssetData(`${coinKey}_USDT`);
    fetchAssetDataLocal(`${coinKey}_USDT`);
    fetchLatestTrades(`${coinKey}_USDT`);
    fetchCurrentOrders(`${coinKey}_USDT`);

    //  清除旧定时器，避免重复轮询
    if (pollingIntervalId) {
        clearInterval(pollingIntervalId);
    }

    //  每秒轮询刷新 K 线 和 行情
    pollingIntervalId = setInterval(() => {
        // fetchMarketInfo(coinKey);
        // fetchKlineData(coinKey);
        // fetchOrderBook(coinKey);
        // fetchOrderBookWithLocal(coinKey);
        // fetchLatestTrades(`${coinKey}_USDT`);
    }, 5000);
}

function renderKline(klineData) {
    const chart = echarts.init(document.getElementById('klineChart'));
    const times = klineData.map(i => i[0]);
    const values = klineData.map(i => i.slice(1));
    chart.setOption({
        tooltip: {
            trigger: 'axis',
            formatter: function (params) {
                const d = params[0];
                const val = d.data;
                return `${d.axisValue}<br/>开盘: ${val[0]}<br/>最高: ${val[1]}<br/>最低: ${val[2]}<br/>收盘: ${val[3]}`;
            }
        },
        xAxis: {type: 'category', data: times},
        yAxis: {type: 'value'},
        series: [{
            type: 'candlestick',
            data: values,
            itemStyle: {color: '#ec0000', color0: '#00da3c', borderColor: '#8A0000', borderColor0: '#008F28'}
        }]
    });
}

function placeOrder(side) {
    const pairId = `${currentCoin}_USDT`;
    const type = document.getElementById('orderType').value === 'limit' ? 0 : 1;
    const price = parseFloat(document.getElementById('priceInput').value);
    const amount = parseFloat(document.getElementById('amountInput').value);

    if (!amount || (type === 0 && !price)) {
        alert('请输入正确的' + (type === 0 ? '价格和数量' : '数量'));
        return;
    }

    const payload = {
        pair_id: pairId,
        type: type,
        side: side,
        price: type === 0 ? price : 0, // 市价不填价格
        amount: amount
    };

    axios.post('http://localhost:8888/order/create-order', payload)
        .then(res => {
            if (res.data.code === 200) {
                alert((side === 0 ? '买入' : '卖出') + '成功 ✅');
                // 刷新页面关键模块（不要整页刷新）
                fetchCurrentOrders(pairId);
                fetchAssetData(pairId);
                fetchAssetDataLocal(pairId);
                fetchOrderBook(currentCoin);
                fetchOrderBookWithLocal(currentCoin);
                fetchLatestTrades(pairId);
                fetchMarketInfo(currentCoin);
                fetchKlineData(currentCoin); // 可选：刷新K线图
                // ✅ 清空输入框
                document.getElementById('priceInput').value = '';
                document.getElementById('amountInput').value = '';
            } else {
                alert(`下单失败 ❌：${res.data.message}`);
            }
        })
        .catch(err => {
            console.error(err);
            alert('网络请求异常或服务器错误 ❌');
        });
}

function cancelOrder(orderId) {
    const pairId = `${currentCoin}_USDT`;


    if (!orderId) {
        alert('订单id不能为空');
        return;
    }

    if (confirm("您确定要撤单吗？")) {
        const payload = {
            order_id: orderId,
        };

        axios.post('http://localhost:8888/order/cancel-order', payload)
            .then(res => {
                if (res.data.code === 200) {
                    alert("撤单成功");
                    // 刷新页面关键模块（不要整页刷新）
                    fetchCurrentOrders(pairId);
                    fetchAssetData(pairId);
                    fetchAssetDataLocal(pairId);
                    fetchOrderBook(currentCoin);
                    fetchOrderBookWithLocal(currentCoin);
                    fetchLatestTrades(pairId);
                    fetchMarketInfo(currentCoin);
                    fetchKlineData(currentCoin); // 可选：刷新K线图
                } else {
                    alert(`撤单失败 ❌：${res.data.message}`);
                }
            })
            .catch(err => {
                console.error(err);
                alert('网络请求异常或服务器错误 ❌');
            });
    }
}

function loadTab(tabName) {
    if (tabName === 'current') {
        fetchCurrentOrders(`${currentCoin}_USDT`);
    } else if (tabName === 'history') {
        fetchHistoryOrders(`${currentCoin}_USDT`);
    } else if (tabName === 'trades') {
        fetchTradeRecords(`${currentCoin}_USDT`)
    }
}

document.getElementById('coinPairSelect').addEventListener('change', function () {
    const coinKey = this.value;
    updatePage(coinKey);
});

window.addEventListener('DOMContentLoaded', () => {
    fetchCoinList();
});
