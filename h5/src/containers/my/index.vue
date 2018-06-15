<template>
  <div>
    <user></user>
    <orders></orders>
  </div>
</template>
<script>
import Orders from '../order/orders.vue';
import User from './user.vue';
import store from '@/store';

export default {
  components: {
    Orders,
    User
  },
  beforeRouteEnter(to, from, next) {
    // 判断是否登录
    const isLogin = !!store.state.token;

    !isLogin ? next({name: 'prelogin', query: { redirect: to.name }}) : next();
  }
}
</script>

