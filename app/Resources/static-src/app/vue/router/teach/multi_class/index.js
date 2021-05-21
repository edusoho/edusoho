export default [
  {
    path: '/',
    name: 'MultiClass',
    component: () => import('app/vue/views/teach/multi_class/index.vue')
  },
  {
    path: '/create_course',
    name: 'MultiClassCreateCourse',
    component: () => import('app/vue/views/teach/multi_class/create_course/index.vue')
  },
  {
    path: '/course_manage',
    name: 'MultiClassCourseManage',
    component: () => import('app/vue/views/teach/multi_class/course_manage/index.vue'),
    children: [
      {
        path: '',
        component: () => import('app/vue/views/teach/multi_class/course_manage/class-info.vue'),
      },
      {
        path: 'student_manage',
        component: () => import('app/vue/views/teach/multi_class/course_manage/student-manage.vue'),
      },
      {
        path: 'homework_review',
        component: () => import('app/vue/views/teach/multi_class/course_manage/homework-review.vue'),
      },
      {
        path: 'data_preview',
        component: () => import('app/vue/views/teach/multi_class/course_manage/data-preview.vue'),
      }
    ]
  }
];
