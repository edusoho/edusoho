import { createVueApp } from 'app/vue/utils/vue-creator';

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
    component: () => import( 'app/js/question-bank/testpaper/create/create.vue'),
    props: function () {
      return {
        itemBankId: document.getElementById('itemBankId').value,
      }
    },
  },
  {
    path: '/update/:id',
    name: 'update',
    component: () => import( 'app/js/question-bank/testpaper/create/create.vue'),
    props: function (route) {
      return {
        itemBankId: document.getElementById('itemBankId').value,
        id: route.params.id
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

createVueApp('#app', routes);

$('.nav.nav-tabs').lavaLamp({setOnClick: false});