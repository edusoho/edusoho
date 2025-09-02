export default [
  {
    // 人脸识别 判断是否设置了人脸识别
    name: 'settingsFace',
    url: '/settings/face',
    method: 'GET',
  },
  {
    // 云短信服务
    name: 'sitePlugins',
    url: '/site_plugins/{pluginName}',
    method: 'GET',
  },
  {
    // 云短信服务
    name: 'settingsCloud',
    url: '/settings/cloud',
    method: 'GET',
  },
  {
    // 更多课程分类
    name: 'getCourseCategories',
    url: '/categories/course',
  },
  {
    // 更多班级分类
    name: 'getClassCategories',
    url: '/categories/classroom',
  },
  {
    // 更多获取筛选信息
    name: 'getSelectItems',
    url: '/pages/h5/settings/course',
  },
  {
    // 获取全局设置
    name: 'getSettings',
    url: '/settings/{type}',
    method: 'GET',
    disableLoading: true,
  },
  {
    // 获取全局设置
    name: 'getAllSettings',
    url: '/settings',
    method: 'GET',
    disableLoading: true,
  },
  {
    // 后台配置保存草稿数据
    name: 'saveDraftDate',
    url: '/pages/h5/settings/{type}/draft',
    method: 'GET',
  },
  {
    // 首页正式数据
    name: 'discoveries',
    url: '/pages/h5/discoveries',
    method: 'GET',
  },
  {
    // 拼团数据
    name: 'groupon',
    url: '/page/h5/groupon/{activityId}',
    method: 'GET',
  },
  {
    // 上传文件
    name: 'updateFile',
    url: '/files',
    method: 'POST',
  },
  {
    name: 'weixinConfig',
    url: '/settings/weixinConfig',
    method: 'GET',
    disableLoading: true,
  },
  {
    name: 'loginConfig',
    url: '/settings/login',
    method: 'GET',
    disableLoading: true,
  },
  {
    name: 'wechatConfig',
    url: '/setting/wechat',
    method: 'GET',
    disableLoading: true,
  },
  {
    name: 'wechatJsSdkConfig',
    url: '/setting/weixinConfig',
    method: 'GET',
    disableLoading: true,
  },
  {
    name: 'wechatSubscribe',
    url: '/settings/wechat_message_subscribe',
    method: 'GET',
    disableLoading: true,
  },
  {
    name: 'wechatTemplate',
    url: '/template',
    method: 'GET',
    disableLoading: true,
  },
  {
    name: 'resetPassword',
    url: '/UserResetPassword',
    method: 'POST',
    disableLoading: true,
  },
];
