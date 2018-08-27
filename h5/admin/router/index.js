import Vue from 'vue';
import store from '@/store';
import * as types from '@admin/store/mutation-types';
import Router from 'vue-router';

Vue.use(Router);
// 路由懒加载 实现代码分离
const routes = [
  {
    path: '/',
    name: 'admin',
    meta: {
      title: '后台配置'
    },
    component: () => import(/* webpackChunkName: "setting" */'@admin/containers/setting/index.vue')
  },
  {
    path: '/preview',
    name: 'preview',
    meta: {
      title: '发现页预览'
    },
    component: () => import(/* webpackChunkName: "preview" */'@admin/containers/preview/index.vue')
  }
];

// 页面刷新，store数据会被清掉，需对token、user重新赋值
if (!store.state.csrfToken) {
  const csrfTag = document.getElementsByTagName('meta')['csrf-token'];
  if (csrfTag && csrfTag.content) {
    store.commit(types.GET_CSRF_TOKEN, csrfTag.content);
  } else {
    // new Error('csrfToken 不存在');
  }
}

const router = new Router({
  routes
});

export default router;
