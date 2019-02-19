<template>
  <div>授权页面</div>
</template>

<script>
import redirectMixin from '@/mixins/saveRedirect';
import * as types from '@/store/mutation-types';
import Api from '@/api';
import { Toast } from 'vant';

export default {
  mixins: [redirectMixin],
  name: 'social-wx',
  data () {
    return {

    }
  },
  async created() {
    let code = location.search.match(/\?code.*&/g);
    if (!code) {
      this.wxLogin();
      return;
    }
    Toast.loading({
      message: '正在登陆'
    });
    code = code[0].slice(6, -1);
    console.log(code)
    await Api.login({
      params: {
        code: code,
        type: 'weixinmob'
      }
    }).then(res => {
      this.$store.commit(types.USER_LOGIN, res);
      Toast.clear()
      Toast.success({
        duration: 2000,
        message: '登录成功'
      });
      this.$router.go(-2);
      this.afterLogin();
    }).catch(err => {
      window.location.href = '/login/bind/weixinmob?os=h5&_target_path='
        + encodeURIComponent(location.pathname + location.hash);
    })
  },
  methods: {
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
