import store from '@/store';

export default {
  beforeRouteEnter(to, from, next) {
    // 判断是否登录
    const isLogin = !!store.state.token;

    if (!isLogin) {
      next({ name: 'prelogin', query: { redirect: to.fullPath } });
      return;
    }
    next();
  }
};
