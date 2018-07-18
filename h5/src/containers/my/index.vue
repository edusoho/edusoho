<template>
  <div>
    <e-loading v-if="isLoading"></e-loading>
    <user></user>
    <orders></orders>
  </div>
</template>
<script>
import Orders from '../order/orders.vue';
import User from './user.vue';
import store from '@/store';
import { mapState } from 'vuex';

export default {
  components: {
    Orders,
    User
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading
    })
  },
  beforeRouteEnter(to, from, next) {
    // 判断是否登录
    const isLogin = !!store.state.token;

    !isLogin ? next({name: 'prelogin', query: { redirect: to.name }}) : next();
  }
}
</script>

