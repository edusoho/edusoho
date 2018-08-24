const config = [
  {
    // 后台配置保存草稿数据
    name: 'saveDraftDate',
    url: '/pages/{portal}/settings/{type}',
    method: 'GET'
  }, {
    name: 'uploadFile',
    url: '/api/files',
    method: 'POST'
  }
];

export default config;
