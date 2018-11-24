export default [
  {
    // 课程详情页
    name: 'getCourseDetail',
    url: '/pages/h5/courses/{courseId}',
    method: 'GET'
  }, {
    // 获取计划目录
    name: 'getCourseLessons',
    url: '/courses/{courseId}/item_with_lessons',
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
    // 获取课程列表数据
    name: 'getCourseList',
    url: '/courses'
  }, {
    // 获取课程搜索列表
    name: 'getCourseSets',
    url: '/course_sets'
  }, {
    name: 'getCourse',
    url: '/course_sets/{courseId}',
    method: 'GET'
  }, {
    name: 'getCourseReviews',
    url: '/courseSet/{id}/reviews',
    method: 'GET'
  }
];
