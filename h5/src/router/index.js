import Vue from 'vue';
import Router from 'vue-router';
import find from './find';
import learning from './learning';
import my from './my';

Vue.use(Router);

const routes = [{
  path: '/',
  redirect: '/find',
  name: 'home',
  component: resolve => require(['@/containers/home.vue'], resolve),
  children: [
    ...find,
    ...learning,
    ...my,
  ],
}, {
  path: '/login',
  name: 'login',
  meta: {
    title: '登录',
  },
  component: resolve => require(['@/containers/login.vue'], resolve),
}, {
  path: '/register',
  name: 'register',
  meta: {
    title: '注册',
  },
  component: resolve => require(['@/containers/register.vue'], resolve),
}];

const router = new Router({
  routes,
});

export default router;
