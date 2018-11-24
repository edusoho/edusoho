export default [
  {
    // 课程详情页
    name: 'getClassroomDetail',
    url: '/pages/h5/classrooms/{classroomId}',
    method: 'GET'
  }, {
    // 加入班级
    name: 'joinClass',
    url: '/classrooms/{classroomId}/members',
    method: 'POST'
  }, {
    name: 'getReviews',
    url: '/classrooms/{classroomId}/reviews',
    method: 'GET'
  }, {
    // 获取课程列表数据
    name: 'getClassList',
    url: '/classrooms'
  }
];
