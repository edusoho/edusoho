const config = [
  {
    // 后台配置获取
    name: 'getDraft',
    url: '/pages/{portal}/settings/{type}',
    method: 'GET'
  }, {
    // 后台配置保存
    name: 'saveDraft',
    headers:{
      'Content-Type': 'application/json'
    },
    url: '/pages/{portal}/settings',
    method: 'POST',
  }, {
    // 删除后台配置
    name: 'deleteDraft',
    url: '/pages/{portal}/settings/{type}',
    method: 'DELETE',
  }, {
    // 上传文件
    name: 'uploadFile',
    url: '/api/files',
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
    method: 'GET',
  }, {
    // 获得二维码
    name: 'getQrcode',
    url: '/qrcode/{route}',
    method: 'GET',
    // noPrefix: true,
  }
];

export default config;
