const config = [
  {
    // 登录
    name: 'login',
    url: '/tokens',
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
  },
  {
    name: 'getCourse',
    url: '/course_sets/{courseId}',
    method: 'GET'
  },
  {
    name: 'discoveries',
    url: '/pages/h5/discoveries',
    method: 'GET'
  },
  {
    name: 'dragCaptcha',
    url: '/drag_captcha',
    method: 'POST'
  },
  {
    name: 'dragValidate',
    url: '/drag_captcha/{token}',
    method: 'GET'
  },
  {
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
    url: '/me/nickname/{nickname}',
    method: 'PATCH'
  }, {
    // 上传文件
    name: 'updateFile',
    url: '/files',
    method: 'POST'
  }, {
    // 我的订单
    name: 'getMyOrder',
    url: '/me/orders',
    method: 'GET'
  }, {
    // 确认订单信息
    name: 'confirmOrder',
    url: '/order_infos',
    method: 'POST'
  }, {
    // 创建订单信息
    name: 'createOrder',
    url: '/orders',
    method: 'POST'
  }, {
    // 创建支付信息
    name: 'createTrade',
    url: '/trades',
    method: 'POST'
  }, {
    // 获取订单信息
    name: 'getOrderDetail',
    url: '/orders/{sn}',
    method: 'GET'
  }, {
    // 课程详情页
    name: 'getCourseDetail',
    url: '/pages/h5/courses/{courseId}',
    method: 'GET'
  }, {
    // 我的学习
    name: 'myStudyState',
    url: '/me/courses',
    method: 'GET'
  }, {
    // 加入课程
    name: 'joinCourse',
    url: '/courses/{id}/members',
    method: 'POST'
  }, {
    // 课时播放
    name: 'getMedia',
    url: '/courses/{courseId}/task_medias/{taskId}',
    method: 'GET'
  }, {
    // 更多获取筛选信息
    name: 'getSelectItems',
    url: '/pages/h5/settings/course'
  }, {
    // 获取课程列表数据
    name: 'getCourseList',
    url: '/courses'
  }, {
    // 获取课程搜索列表
    name: 'getCourseSets',
    url: '/course_sets'
  }, {
    // 获取全局设置
    name: 'getSettings',
    url: '/settings/{type}',
    method: 'GET'
  }
];

export default config;
