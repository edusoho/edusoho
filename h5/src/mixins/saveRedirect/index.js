import activityHandle from '@/mixins/activity/request';

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
      const backUrl = this.$route.query.skipUrl ? decodeURIComponent(this.$route.query.skipUrl) : '';
      const callbackType = this.$route.query.callbackType; // 不能用type, 和人脸识别种的type 冲突。。。
      const activityId = this.$route.query.activityId;
      const callback = decodeURIComponent(this.$route.query.callback);
      const jumpAction = () => {
        if (callbackType) {
          switch (callbackType) {
            case 'marketing':
              activityHandle(activityId, callback);
              break;
            default:
              break;
          }
          return;
        }
        this.$router.replace({ path: redirect, query: { backUrl } });
      };
      setTimeout(jumpAction, 2000);
    }
  }
};
