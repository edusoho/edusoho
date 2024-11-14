import AddTestPaper from './AddTestPaper.vue';
import Vue from 'common/vue';
import { createVueApp } from 'app/vue/utils/vue-creator';

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

new Vue({
  render: createElement => createElement(AddTestPaper)
}).$mount('#addTestPaper');

createVueApp('#assessment-list', routes);
