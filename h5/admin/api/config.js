const config = [
  {
    // 后台配置获取
    name: 'getDraft',
    url: '/pages/{portal}/settings/{type}',
  }, {
    // 后台配置保存
    name: 'saveDraft',
    headers: {
      'Content-Type': 'application/json'
    },
    url: '/pages/{portal}/settings',
    method: 'POST'
  }, {
    // 删除后台配置
    name: 'deleteDraft',
    url: '/pages/{portal}/settings/{type}',
    method: 'DELETE'
  }, {
    // 获取网校、插件版本
    name: 'getMPVersion',
    url: '/settings/miniprogram',
  }, {
    // 上传文件
    name: 'uploadFile',
    url: '/files',
    method: 'POST'
  }, {
    // 获取分类配置
    name: 'getCategories',
    url: '/categories/{groupCode}',
  }, {
    // 获取课程列表数据
    name: 'getCourseList',
    url: '/courses',
  }, {
    // 获取班级列表数据
    name: 'getClassList',
    url: '/classrooms',
  }, {
    // 获取优惠券
    name: 'getCouponList',
    url: '/plugins/coupon/coupon_batches',
  }, {
    // 微营销活动列表数据
    name: 'getMarketingList',
    url: '/marketing_activities',
  }, {
    // 获得二维码
    name: 'getQrcode',
    url: '/qrcode/{route}',
    // noPrefix: true,
  }, {
    name: 'getVipLevels',
    url: '/plugins/vip/vip_levels',
  }
];

export default config;
