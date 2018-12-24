export default [
  {
    name: 'getVipDetail',
    url: '/plugins/vip/pages/h5/vips/{levelId}',
    method: 'GET'
  }, {
    name: 'getVipCourses',
    url: '/plugins/vip/vip_courses?levelId=5&offset=0&sort=-price',
    method: 'GET'
  }
];
