export default [
  {
    path: '/',
    name: 'MultiClass',
    component: () => import(/* webpackChunkName: "app/vue/dist/MultiClass" */ 'app/vue/views/teach/multi_class/index.vue')
  },
  {
    path: '/create',
    name: 'MultiClassCreate',
    component: () => import(/* webpackChunkName: "app/vue/dist/MultiClassCreate" */ 'app/vue/views/teach/multi_class/create/index.vue'),
    meta: {
      keepAlive: true,
    }
  },
  {
    path: '/create_course',
    name: 'MultiClassCreateCourse',
    component: () => import(/* webpackChunkName: "app/vue/dist/CreateCourse" */ 'app/vue/views/teach/multi_class/create_course/index.vue'),
  },
  {
    path: '/manage/:id',
    component: () => import(/* webpackChunkName: "app/vue/dist/CourseManage" */ 'app/vue/views/teach/multi_class/course_manage/index.vue'),
    children: [
      {
        path: '',
        name: 'MultiClassCourseManage',
        component: () => import(/* webpackChunkName: "app/vue/dist/MultiClassCourseManage" */ 'app/vue/views/teach/multi_class/course_manage/class-info.vue'),
        meta: { current: 'class-info' }
      },
      {
        path: 'student_manage',
        name: 'MultiClassStudentManage',
        component: () => import(/* webpackChunkName: "app/vue/dist/MultiClassStudentManage" */ 'app/vue/views/teach/multi_class/course_manage/student-manage.vue'),
        meta: { current: 'student-manage' }
      },
      {
        path: 'homework_review',
        name: 'MultiClassHomewordReview',
        component: () => import(/* webpackChunkName: "app/vue/dist/MultiClassHomewordReview" */ 'app/vue/views/teach/multi_class/course_manage/homework_review.vue'),
        meta: { current: 'homework-review' }
      },
      {
        path: 'data_preview',
        name: 'MultiClassDataPreview',
        component: () => import(/* webpackChunkName: "app/vue/dist/MultiClassDataPreview" */ 'app/vue/views/teach/multi_class/course_manage/data_preview.vue'),
        meta: { current: 'data-preview' }
      }
    ]
  },
  {
    path: '/manage/editor_lesson/:id',
    name: 'MultiClassEditorLesson',
    component: () => import(/* webpackChunkName: "app/vue/dist/MultiClassEditorLesson" */ 'app/vue/views/teach/multi_class/course_manage/editor-lesson.vue')
  },
];
