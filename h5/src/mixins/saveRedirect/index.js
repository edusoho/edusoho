/* 需要到登录权限的页面／组件，跳转前把当前路由记录下来 */
export default {
  data() {
    return {
      redirect: ''
    };
  },
  created() {
    this.redirect = decodeURIComponent(this.$route.fullPath);
  },
  methods: {
    afterLogin() {
      const redirect = this.$route.query.redirect
        ? decodeURIComponent(this.$route.query.redirect) : '/';
      const type = this.$route.query.type;
      const activityId = this.$route.query.activityId;
      const callback = this.$route.query.callback;
      const jumpAction = () => {
        if (type) {
          switch (type) {
            case 'marketing':
              this.activityHandle(activityId, true, callback);
              break;
            default:
              break;
          }
          return;
        }
        this.$router.replace({ path: redirect });
      };
      setTimeout(jumpAction, 2000);
    }
  }
};
