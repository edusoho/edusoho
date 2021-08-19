import Vue from 'common/vue';
import Router from 'vue-router';
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const router = new Router({
  mode: 'hash',
  routes: [
    {
      path: '/',
      name: 'Overview',
      component: () => import(/* webpackChunkName: "app/vue/dist/Overview" */ 'app/vue/views/teach/overview/index.vue')
    },
    {
      path: '/over_time',
      name: 'Overtime',
      component: () => import(/* webpackChunkName: "app/vue/dist/Overtime" */ 'app/vue/views/teach/overview/overtime/index.vue')
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
      path: '/inspection',
      name: 'MultiClassInspection',
      component: () => import(/* webpackChunkName: "app/vue/dist/MultiClassInspection" */ 'app/vue/views/teach/multi_class_inspection/index.vue')
    }
  ]
})

new Vue({
  el: '#app',
  router,
  components: {
    AntConfigProvider
  },
  template: `<ant-config-provider />`
});

