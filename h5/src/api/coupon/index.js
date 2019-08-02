export default [
  {
    // 获取优惠券信息
    name: 'getCouponInfo',
    url: '/plugins/coupon/coupon_batch/{batchToken}',
    method: 'get'
  },
  {
    // 优惠券引流优化领取优惠券
    name: 'pluginsReceiveCoupon',
    url: '/me/coupon',
    method: 'POST'
  },
  {
    // 领取优惠券
    name: 'receiveCoupon',
    url: '/me/coupons',
    method: 'POST'
  }, {
    // 根据渠道查询优惠券
    name: 'searchCoupon',
    url: '/plugins/coupon/channel/h5Mps/coupon_batches?limit=100',
    disableLoading: true
  }, {
    // 判断兑换券券码引流插件是否安装
    name: 'hasPluginInstalled',
    url: '/settings/hasPluginInstalled?pluginCodes=BusinessDrainage',
    disableLoading: true
  }, {
    // 兑换券Check
    name: 'couponCheck',
    url: '/plugins/business_drainage/exchange_ecard_check/{code}',
    disableLoading: true
  }, {
    // 兑换码兑换
    name: 'exchangeCoupon',
    method: 'PATCH',
    url: '/plugins/business_drainage/ecard_operation/inner_exchange/code/{code}',
    disableLoading: true
  }, {
    // 优惠码兑换
    name: 'exchangePreferential',
    method: 'POST',
    url: '/coupons/{code}/actions',
    disableLoading: true
  }
];
