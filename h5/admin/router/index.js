import Vue from 'vue';
// import store from '@/store';
// import * as types from '@/store/mutation-types';
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
// if (localStorage.getItem('token')) {
//   store.commit(types.USER_LOGIN, {
//     token: localStorage.getItem('token'),
//     user: JSON.parse(localStorage.getItem('user'))
//   });
// }

const router = new Router({
  routes
});

// router.beforeEach((to, from, next) => {
//   if (!Object.keys(store.state.settings).length) {
//     // 获取全局设置
//     store.dispatch('getGlobalSettings', { type: 'site' })
//       .then(res => {
//         if (to.name === 'find') {
//           to.meta.title = res.name;
//         }
//         next();
//       });
//   } else if (['register', 'login', 'protocol', 'find'].includes(to.name)) {
//     to.meta.title = store.state.settings.name;
//     next();
//   } else {
//     next();
//   }
// });
export default router;
