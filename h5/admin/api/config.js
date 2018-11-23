const config = [
  {
    // 后台配置获取
    name: 'getDraft',
    url: '/pages/{portal}/settings/{type}',
    method: 'GET'
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
    method: 'GET'
  }, {
    // 上传文件
    name: 'uploadFile',
    url: '/files',
    method: 'POST'
  }, {
    // 获取分类配置
    name: 'getCategories',
    url: '/categories/{groupCode}',
    method: 'GET'
  }, {
    // 获取课程列表数据
    name: 'getCourseList',
    url: '/courses',
    method: 'GET'
  }, {
    // 获取班级列表数据
    name: 'getClassList',
    url: '/classrooms',
    method: 'GET'
    // 微营销活动列表数据
    name: 'getMarketingList',
    url: '/marketing_activities',
    methods: 'GET'
  }, {
    // 获得二维码
    name: 'getQrcode',
    url: '/qrcode/{route}',
    method: 'GET'
    // noPrefix: true,
  }
];

export default config;
