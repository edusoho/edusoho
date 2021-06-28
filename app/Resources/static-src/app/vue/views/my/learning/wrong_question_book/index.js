import Vue from 'common/vue';
import Router from 'vue-router';
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const routes = [
  {
    path: '/',
    component: () => import(/* webpackChunkName: "app/vue/dist/WrongQuestionBook" */ 'app/vue/views/my/learning/wrong_question_book/index.vue'),
    children: [
      {
        path: '',
        name: 'CourseWrongQuestion',
        meta: { current: 'course' },
        component: () => import(/* webpackChunkName: "app/vue/dist/WrongQuestionBook" */ 'app/vue/views/my/learning/wrong_question_book/course.vue')
      },
      {
        path: 'classroom',
        name: 'ClassroomWrongQuestion',
        meta: { current: 'classroom' },
        component: () => import(/* webpackChunkName: "app/vue/dist/WrongQuestionBook" */ 'app/vue/views/my/learning/wrong_question_book/classroom.vue')
      },
      {
        path: 'question_bank',
        name: 'QuestionBankWrongQuestion',
        meta: { current: 'question-bank' },
        component: () => import(/* webpackChunkName: "app/vue/dist/WrongQuestionBook" */ 'app/vue/views/my/learning/wrong_question_book/question_bank.vue')
      }
    ]
  }
];

const router = new Router({
  mode: 'hash',
  routes
});

new Vue({
  el: '#app',
  components: {
    AntConfigProvider
  },
  router,
  template: '<ant-config-provider />'
});

