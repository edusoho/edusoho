export default [
  {
    // 领取优惠券
    name: 'receiveCoupon',
    url: '/plugins/coupon/coupon_batches/{token}/receivers',
    method: 'POST'
  }, {
    // 根据渠道查询优惠券
    name: 'searchCoupon',
    url: '/plugins/coupon/channel/h5Mps/coupon_batches',
    disableLoading: true
  }
];
