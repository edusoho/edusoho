export default [
  {
    // 课程详情页
    name: 'getClassroomDetail',
    url: '/pages/h5/classrooms/{classroomId}',
    method: 'GET'
  }, {
    // 加入班级
    name: 'joinCourse',
    url: '/classrooms/{id}/members',
    method: 'POST'
  }
];
