const config = [
  {
    // 后台配置保存草稿数据
    name: 'getDraft',
    url: '/pages/{portal}/settings/{type}',
    method: 'GET'
  }, {
    name: 'saveDraft',
    url: '/pages/{portal}/settings',
    method: 'POST',
    headers:{
      'Content-Type': 'application/json'
    },
  }, {
    name: 'uploadFile',
    url: '/api/files',
    method: 'POST'
  }, {
    name: 'getCategories',
    url: '/categories/{groupCode}',
    method: 'GET'
  }
];

export default config;
