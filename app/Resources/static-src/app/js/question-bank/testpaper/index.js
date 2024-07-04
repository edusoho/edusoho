import Vue from 'common/vue';
import Router from 'vue-router';
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const routes = [
  {
    path: '/',
    name: 'list',
    component: () => import('app/js/question-bank/testpaper/list/list.vue'),
    props: function () {
      return {
        itemBankId: document.getElementById('itemBankId').value,
      }
    },
  },
  {
    path: '/create',
    name: 'create',
    component: () => import( 'app/js/question-bank/testpaper/create/create.vue')
  }
];
const router = new Router({
  routes
});

Vue.config.productionTip = false;

new Vue({
  el: '#app',
  components: {
    AntConfigProvider
  },
  router,
  template: '<ant-config-provider/>'
});
