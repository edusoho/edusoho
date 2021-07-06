import Vue from 'common/vue';
import Router from 'vue-router';
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const routes = [
  {
    path: '/',
    name: 'ItemBankExerciseManageWrongQuestion',
    component: () => import(/* webpackChunkName: "app/vue/dist/ItemBankExerciseManageWrongQuestion" */ 'app/vue/views/item_bank_exercise_manage/wrong_question/index.vue')
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

