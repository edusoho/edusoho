import activityHandle from '@/mixins/activity/request';
import Api from '@/api';
import * as types from '@/store/mutation-types';
import { mapState, mapMutations } from 'vuex';

/* 需要到登录权限的页面／组件，跳转前把当前路由记录下来 */
export default {
  data() {
    return {
      redirect: '',
    };
  },
  computed: {
    ...mapState({
      user: state => state.user,
    }),
  },
  created() {
    this.redirect = decodeURIComponent(this.$route.fullPath);
  },
  methods: {
    ...mapMutations([types.SET_MOBILE_BIND]),
    afterLogin() {
      this.checkMobileBind()
        .then(({ is_bind_mobile, mobile_bind_mode }) => {
          // res.mobile_bind_mode: constraint：强制绑定，option：非强制绑定，closed：不绑定
          this[types.SET_MOBILE_BIND]({ is_bind_mobile, mobile_bind_mode });
          
          if (!is_bind_mobile && mobile_bind_mode !== 'closed') {
            this.$router.replace({
              name: 'binding',
              query: {
                redirect: this.$route.query.redirect,
              },
            });

            return;
          }

          setTimeout(this.jumpAction, 1000);
        })
        .catch(err => {
          setTimeout(this.jumpAction, 1000);
        });
    },
    jumpAction() {
      /* eslint handle-callback-err: 0 */
      const redirect = this.$route.query.redirect
        ? decodeURIComponent(this.$route.query.redirect)
        : '/';
      const backUrl = this.$route.query.skipUrl
        ? decodeURIComponent(this.$route.query.skipUrl)
        : '';

      const callbackType = this.$route.query.callbackType; // 不能用type, 和人脸识别种的type 冲突。。。
      const activityId = this.$route.query.activityId;
      const callback = decodeURIComponent(this.$route.query.callback);

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

      if (backUrl) {
        this.$router.replace({
          path: redirect,
          query: { backUrl },
        });
        return;
      }

      this.$router.replace({ path: redirect });
    },
    checkMobileBind() {
      return Api.mobileBindCheck({
        query: { userId: this.user.id },
      });
    },
  },
};
