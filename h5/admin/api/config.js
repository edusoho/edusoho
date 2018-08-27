const config = [
  {
    // 后台配置获取
    name: 'getDraft',
    url: '/pages/{portal}/settings/{type}',
    method: 'GET'
  }, {
    // 后台配置保存
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
    // 获取分类配置
    name: 'getCategories',
    url: '/categories/{groupCode}',
    method: 'GET'
  }, {
    // 获取课程列表数据
    name: 'getCourseList',
    url: '/courses',
    method: 'GET',
  },
];

export default config;
