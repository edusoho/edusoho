export default {
  methods: {
    isAuthorized(err) {
      if (err.code === 11) {
        this.$router.replace(
          {
            // 待解决：replace 会导致返回按钮的功能有问题
            name: 'login',
            query: { redirect: this.$router.currentRoute.fullPath },
          },
          () => {
            window.location.reload(); // redirect 为 '/' 时，需要刷新才能进入对应页面的问题
          },
        );
      } else {
        this.$toast(err.message);
      }
    },
  },
};
