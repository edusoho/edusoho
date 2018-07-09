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
    name: 'getCourses',
    url: '/course_sets',
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
  },
  {
    // 修改头像
    name: 'setAvatar',
    url: '/me/avatar',
    method: 'POST'
  }, {
    // 我的订单
    name: 'getMyOrder',
    url: '/me/orders?type=course',
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
  }
];

export default config;
