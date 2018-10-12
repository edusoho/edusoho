import Vue from 'vue';
import store from '@admin/store';
import * as types from '@admin/store/mutation-types';
import Router from 'vue-router';

Vue.use(Router);
// 路由懒加载 实现代码分离
const routes = [
  {
    path: '/',
    name: 'h5Setting',
    meta: {
      title: 'h5后台配置'
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
  },
  {
    path: '/miniprogram',
    name: 'miniprogramSetting',
    meta: {
      title: '小程序后台配置'
    },
    component: () => import(/* webpackChunkName: "miniprogramSetting" */'@admin/containers/setting/index.vue')
  }
];

const env = process.env.NODE_ENV;
console.log('process.env', env);
// csrfToken 赋值
if (!store.state.csrfToken && env === 'production') {
  const csrfTag = window.parent.document.getElementsByTagName('meta')['csrf-token'];
  if (csrfTag && csrfTag.content) {
    store.commit(types.GET_CSRF_TOKEN, csrfTag.content);
  } else {
    new Error('csrfToken 不存在');
  }
}

const router = new Router({
  routes
});

export default router;
