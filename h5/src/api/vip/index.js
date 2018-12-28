export default [
  {
    name: 'getVipDetail',
    url: '/plugins/vip/pages/h5/vips/{levelId}',
    method: 'GET'
  }, {
    name: 'getVipCourses',
    url: '/plugins/vip/vip_courses?sort=-price',
    method: 'GET'
  }, {
    name: 'getVipClasses',
    url: '/plugins/vip/vip_classrooms?sort=-price',
    method: 'GET'
  }, {
    name: 'getVipLevels',
    url: '/plugins/vip/vip_levels',
    method: 'GET'
  }
];
