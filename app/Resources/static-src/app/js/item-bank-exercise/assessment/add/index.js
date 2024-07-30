import AddTestPaper from './AddTestPaper.vue';
import TestPaperTable from './TestPaperTable.vue';
import Vue from 'common/vue';
import Router from 'vue-router';
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const routes = [
  {
    path: '/',
    name: 'list',
    component: () => import('./TestPaperTable.vue'),
    props: function () {
      return {
        exerciseId: document.getElementById('exerciseId').value,
        moduleId: document.getElementById('moduleId').value,
        itemBankId: document.getElementById('itemBankId').value,
      }
    },
  },
  {
    path: '/preview/:id',
    name: 'preview',
    component: () => import( 'app/js/question-bank/testpaper/preview/Preview.vue'),
    props: function (route) {
      return {
        itemBankId: document.getElementById('itemBankId').value,
        id: route.params.id
      }
    },
  }
];

const router = new Router({
  mode: 'hash',
  routes
});

new Vue({
  render: createElement => createElement(AddTestPaper)
}).$mount('#addTestPaper');

new Vue({
  el: '#assessment-list',
  components: {
    AntConfigProvider
  },
  router,
  template: '<ant-config-provider/>'
});