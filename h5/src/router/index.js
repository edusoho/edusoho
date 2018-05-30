import Vue from 'vue';
import Router from 'vue-router';
import store from '@/store';
import * as types from '@/store/mutation-types';

import HelloWorld from '@/components/HelloWorld';

Vue.use(Router);

const routes = [{
  path: '/',
  name: 'HelloWorld',
  component: HelloWorld,
}, {
  path: '/login',
  name: 'login',
  meta: {
    title: '登录',
  },
  component: resolve => require(['@/containers/login.vue'], resolve),
}];


// 页面刷新时，重新赋值token
if (localStorage.getItem('token')) {
  store.commit(types.USER_LOGIN, {
    token: localStorage.getItem('token'),
    user: JSON.parse(localStorage.getItem('user')),
  });
}

const router = new Router({
  routes,
});

export default router;
