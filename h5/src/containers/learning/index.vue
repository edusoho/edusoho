<template>
  <div>learning</div>
</template>
<script>
  import store from '@/store';
  export default {
    beforeRouteEnter(to, from, next) {
      // 判断是否登录
      const isLogin = !!store.state.token;
      !isLogin ? next({name: 'prelogin',query: { redirect: to.name }}) : next();
    }
  }
</script>
