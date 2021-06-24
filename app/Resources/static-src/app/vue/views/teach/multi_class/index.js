import Vue from 'common/vue';
import Router from 'vue-router';
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const router = new Router({
  mode: 'hash',
  routes: [
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
      redirect: {
        name: 'MultiClassCourseManage'
      },
      component: () => import(/* webpackChunkName: "app/vue/dist/CourseManage" */ 'app/vue/views/teach/multi_class/course_manage/index.vue'),
      children: [
        {
          path: 'class_info',
          name: 'MultiClassCourseManage',
          component: () => import(/* webpackChunkName: "app/vue/dist/MultiClassCourseManage" */ 'app/vue/views/teach/multi_class/course_manage/class_info/index.vue'),
          meta: { current: 'class_info' }
        },
        {
          path: 'student_manage',
          name: 'MultiClassStudentManage',
          component: () => import(/* webpackChunkName: "app/vue/dist/MultiClassStudentManage" */ 'app/vue/views/teach/multi_class/course_manage/student_manage/index.vue'),
          meta: { current: 'student_manage' }
        },
        {
          path: 'homework_review',
          name: 'MultiClassHomewordReview',
          component: () => import(/* webpackChunkName: "app/vue/dist/MultiClassHomeworkReview" */ 'app/vue/views/teach/multi_class/course_manage/homework_review/index.vue'),
          meta: { current: 'homework_review' }
        },
        {
          path: 'data_preview',
          name: 'MultiClassDataPreview',
          component: () => import(/* webpackChunkName: "app/vue/dist/MultiClassDataPreview" */ 'app/vue/views/teach/multi_class/course_manage/data_preview/index.vue'),
          meta: { current: 'data_preview' }
        }
      ]
    },
    {
      path: '/manage/editor_lesson/:id',
      name: 'MultiClassEditorLesson',
      component: () => import(/* webpackChunkName: "app/vue/dist/MultiClassEditorLesson" */ 'app/vue/views/teach/multi_class/course_manage/editor-lesson.vue')
    },
  ]
})

window.CKEDITOR_BASEPATH = app.basePath + '/static-dist/libs/es-ckeditor/';

new Vue({
  el: '#app',
  router,
  components: {
    AntConfigProvider
  },
  template: `<ant-config-provider />`
});

