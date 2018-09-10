/* 需要到登录权限的页面／组件，跳转前把当前路由记录下来 */
export default {
  data() {
    return {
      redirect: ''
    };
  },
  created() {
    this.redirect = decodeURIComponent(this.$route.fullPath);
  }
};
