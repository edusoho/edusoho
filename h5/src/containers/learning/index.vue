<template>
  <div class="e-learn">
    <emptyCourse></emptyCourse>
    <lazyLoading></lazyLoading>
  </div>
</template>
<script>
  import store from '@/store';
  import emptyCourse from './emptyCourse/emptyCourse.vue';
  import lazyLoading from '../components/e-lazy-loading/e-lazy-loading.vue';
  export default {
    beforeRouteEnter(to, from, next) {
      // 判断是否登录
      const isLogin = !!store.state.token;

      !isLogin ? next({name: 'prelogin',query: { redirect: to.name }}) : next();
    },
    components: {
      emptyCourse,
      lazyLoading,
    }
  }
</script>
