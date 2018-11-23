export default [
  {
    // 人脸识别 判断是否设置了人脸识别
    name: 'settingsFace',
    url: '/settings/face',
    method: 'GET'
  }, {
    // 更多获取筛选信息
    name: 'getSelectItems',
    url: '/pages/h5/settings/course'
  }, {
    // 获取全局设置
    name: 'getSettings',
    url: '/settings/{type}',
    method: 'GET'
  }, {
    // 后台配置保存草稿数据
    name: 'saveDraftDate',
    url: '/pages/h5/settings/{type}/draft',
    method: 'GET'
  }, {
    // 首页正式数据
    name: 'discoveries',
    url: '/pages/h5/discoveries',
    method: 'GET'
  }, {
    // 上传文件
    name: 'updateFile',
    url: '/files',
    method: 'POST'
  }
];
