import store from '@/store';

export default [{
  path: '/my/courses/learning',
  name: 'learning',
  meta: {
    title: '我的学习'
  },
  beforeEnter(to, from, next) {
    // 判断是否登录
    const isLogin = !!store.state.token;

    if (!isLogin) {
      next({ name: 'prelogin', query: { redirect: to.name } });
    }
    next();
  },
  component: () => import(/* webpackChunkName: "learning" */'@/containers/learning/index.vue')
}];
