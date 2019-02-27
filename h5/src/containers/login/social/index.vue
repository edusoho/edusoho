<template>
  <div></div>
</template>

<script>
import activityMixin from '@/mixins/activity';
import redirectMixin from '@/mixins/saveRedirect';
import * as types from '@/store/mutation-types';
import Api from '@/api';
import { Toast } from 'vant';
import { mapMutations, mapState } from 'vuex';

export default {
  mixins: [activityMixin, redirectMixin],
  name: 'social-wx',
  async created() {
    if (this.$store.state.token) {
      // 判断非首次登陆后的跳转
      const firstDirect = localStorage.getItem('first_direct')
      if (firstDirect == 1) {
        this.$router.replace({
          path: this.$route.query.redirect || '/',
        });
        return;
      }

      // 判断首次登陆后的跳转
      Toast.loading({
        message: '请稍后'
      });
      this.afterLogin();
      localStorage.setItem('first_direct', 1);
      return;
    }
    // 获取微信绑定状态
    const socialBinded_wx = localStorage.getItem('socialBinded_wx')
      ? JSON.parse(localStorage.getItem('socialBinded_wx'))
      : this.socialBinded['wx'];

    this.setSocialStatus({ key: 'wx', status: socialBinded_wx });

    let code = location.search.match(/\?code.*&/g);

    if (!code) {
      localStorage.setItem('first_direct', 0);
      this.wxLogin();
      return;
    }
    Toast.loading({
      message: '正在登录'
    });
    code = code[0].slice(6, -1);
    await Api.login({
      params: {
        code: code,
        type: 'weixinmob'
      }
    }).then(res => {
      this.userLogin(res);
      Toast.clear()
      Toast.success({
        duration: 2000,
        message: '登录成功'
      });
      let routerDepth = this.socialBinded.wx ? -2 : -7;
      if (this.$route.query.callbackType) {
        routerDepth = routerDepth + 1;
      }
      localStorage.setItem('socialBinded_wx', true);
      this.$router.go(routerDepth);
    }).catch(err => {
      // 更新微信绑定状态
      const socialBinded_wx = false;
      localStorage.setItem('socialBinded_wx', socialBinded_wx);

      window.location.href = '/login/bind/weixinmob?os=h5&_target_path='
        + encodeURIComponent(location.pathname + location.hash);
    })
  },
  computed: {
    ...mapState(['socialBinded']),
  },
  methods: {
    ...mapMutations({
      setSocialStatus: types.SET_SOCIAL_STATUS,
      userLogin: types.USER_LOGIN,
    }),
    wxLogin() {
      Toast.loading({
        message: '请稍后'
      });
      let redirectUrl = encodeURIComponent(location.origin + location.pathname + location.hash);
      window.location.href = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='
        + this.$route.query.weixinmob_key
        + '&redirect_uri='
        + redirectUrl
        + '&response_type=code&scope=snsapi_base&state=123&connect_redirect=1#wechat_redirect';
    }
  }
}
</script>
