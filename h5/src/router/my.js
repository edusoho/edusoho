import store from '@/store';

export default [{
  path: '/my/orders',
  name: 'my',
  meta: {
    title: '我的'
  },
  beforeEnter(to, from, next) {
    // 判断是否登录
    const isLogin = !!store.state.token;

    if (!isLogin) {
      next({ name: 'prelogin', query: { redirect: to.name } });
    }
    next();
  },
  component: () => import(/* webpackChunkName: "my" */'@/containers/my/index.vue')
}];
