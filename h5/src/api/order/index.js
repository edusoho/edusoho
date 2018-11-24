export default [
  {
    // 我的订单
    name: 'getMyOrder',
    url: '/me/orders',
    method: 'GET'
  }, {
    // 确认订单信息
    name: 'confirmOrder',
    url: '/order_infos',
    method: 'POST'
  }, {
    // 创建订单信息
    name: 'createOrder',
    url: '/orders',
    method: 'POST'
  }, {
    // 创建支付信息
    name: 'createTrade',
    url: '/trades',
    method: 'POST'
  }, {
    // 获取订单信息
    name: 'getOrderDetail',
    url: '/orders/{sn}',
    method: 'GET'
  }, {
    // 获取微信支付信息
    name: 'getTrade',
    url: '/trades/{tradesSn}',
    method: 'GET'
  }
];
