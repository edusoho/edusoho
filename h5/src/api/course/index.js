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
    // 优化目录获取计划结构
    name: 'getOptimizationCourseLessons',
    url: '/courses/{courseId}/item_with_lessons?format=tree&onlyPublished=1',
    method: 'GET'
  },
  {
    // 加入课程
    name: 'joinCourse',
    url: '/courses/{id}/members',
    method: 'POST'
  }, {
    // 课时播放
    name: 'getMedia',
    url: '/courses/{courseId}/task_medias/{taskId}',
    method: 'GET'
  },
  {
    // 下次学习课时
    name: 'getNextStudy',
    url: '/me/course_learning_progress/{courseId}',
    method: 'GET',
    disableLoading: true
  }, {
    // 获取课程列表数据
    name: 'getCourseList',
    url: '/courses'
  }, {
    // 获取课程搜索列表
    name: 'getCourseSets',
    url: '/course_sets'
  }, {
    // 根据计划 id 查询计划详情
    name: 'getCourse',
    url: '/course_sets/{courseId}',
    method: 'GET'
  }, {
    // 获取课程评论
    name: 'getCourseReviews',
    url: '/courseSet/{id}/reviews',
    method: 'GET'
  }, {
    // 根据课程查询计划
    name: 'getCourseByCourseSet',
    url: '/course_sets/{id}/courses',
    method: 'GET',
    disableLoading: true
  }, {
    // 退出课程
    name: 'deleteCourse',
    url: '/me/course_members/{id}',
    method: 'DELETE',
    disableLoading: true
  }
];
