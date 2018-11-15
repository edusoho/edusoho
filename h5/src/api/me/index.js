export default [
  {
    // 登录
    name: 'login',
    url: '/tokens',
    method: 'POST'
  }, {
    // 人脸识别 判断用户是否存在
    name: 'getUserIsExisted',
    url: '/users/{type}',
    method: 'GET'
  }, {
    // 人脸识别 创建register的session接口
    name: 'getSessions',
    url: '/face_sessions',
    method: 'POST'
  }, {
    // 人脸识别 获取register的session，确认人脸识别认证结果, 轮询
    name: 'faceSession',
    url: '/face_sessions/{sessionId}',
    method: 'GET'
  }, {
    // 人脸识别 图片上传最终的结果
    name: 'finishUploadResult',
    url: '/face_sessions/{sessionId}/finish_upload_results',
    method: 'POST'
  }, {
    // 新增用户短信验证码
    name: 'getSmsCenter',
    url: '/sms_center',
    method: 'POST'
  }, {
    // 新增用户
    name: 'addUser',
    url: '/user',
    method: 'POST'
  }, {
    // 获取滑动验证参数
    name: 'dragCaptcha',
    url: '/drag_captcha',
    method: 'POST'
  }, {
    // 滑动验证吗数据验证
    name: 'dragValidate',
    url: '/drag_captcha/{token}',
    method: 'GET'
  }, {
    // 获取我的个人信息
    name: 'getUserInfo',
    url: '/me',
    method: 'GET'
  }, {
    // 修改头像
    name: 'setAvatar',
    url: '/me',
    method: 'PATCH'
  }, {
    // 修改昵称
    name: 'setNickname',
    url: '/me/nicknames/h5',
    method: 'PATCH'
  }, {
    // 我的学习
    name: 'myStudyState',
    url: '/me/courses',
    method: 'GET'
  }
];
