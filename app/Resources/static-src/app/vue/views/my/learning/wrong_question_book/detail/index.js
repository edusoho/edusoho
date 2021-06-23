import Vue from 'common/vue';
import Router from 'vue-router';
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const routes = [
  {
    path: '/target_type/:target_type/target_id/:id',
    name: 'WrongQuestionBookDetail',
    component: () => import(/* webpackChunkName: "app/vue/dist/WrongQuestionBookDetail" */ 'app/vue/views/my/learning/wrong_question_book/detail/index.vue')
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

