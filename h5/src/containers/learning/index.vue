<template>
  <div class="e-learn">
    <emptyCourse></emptyCourse>
    <lazyLoading></lazyLoading>
  </div>
</template>
<script>
  import emptyCourse from './emptyCourse/emptyCourse.vue';
  import lazyLoading from '../components/e-lazy-loading/e-lazy-loading.vue';
  export default {
    components: {
      emptyCourse,
      lazyLoading,
    },
    beforeRouteEnter(to, from, next) {
      // 判断是否登录
      const isLogin = !!this.$store.state.token;

      !isLogin ? next({name: 'prelogin',query: { redirect: to.name }}) : next();
    },
  }
</script>
