const config = [
  {
    name: 'login',
    url: '/tokens',
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
    name: 'course',
    url: '/course?limit=4&sort=-studentNum',
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
    name: 'getUserInfo',
    url: '/me',
    method: 'GET'
  }
];

export default config;
