<template>
  <div class="prelogin">
    <img class='prelogin-img' src="static/images/noLoginEmpty.png"></image>
    <span class='prelogin-text'>登录后查看更多信息</span>
    <van-button type="default"
      class="prelogin-btn" @click.native="goLogin">立即登录</van-button>
  </div>
</template>
<script>
  import { Toast } from 'vant';
  import Api from '@/api'
  import * as types from '@/store/mutation-types';

  export default {
    async beforeCreate(){
      let code = location.search.match(/\?code.*&/g);
      if (code) {
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
          const redirect = this.$route.query.redirect || '/';
          const jumpAction = () => {
            this.$router.replace({path: redirect});
          }
          setTimeout(jumpAction, 2000);
        }).catch(err => {
          window.location.href = '/login/bind/weixinmob?os=h5&_target_path='
                  +
                  encodeURIComponent('/h5/#/prelogin');
        })
      }
    },
  methods: {
    goLogin() {
      if (this.isWeixin()) {
        Toast.loading({
          message: '请稍后'
        });
        this.redirectIfWeChatEnabled()
      }

    },
    goOriginLogin(){
      this.$router.push({
        name: 'login',
        query: {
          redirect: this.$route.query.redirect || ''
        }
      })
    },
    isWeixin(){
      const ua = navigator.userAgent.toLowerCase();
      return (ua.match(/MicroMessenger/i) == 'micromessenger') ? true : false;
    },
    async redirectIfWeChatEnabled() {
      Api.loginConfig({}).then(res => {
        if (res.weixinmob_enabled == 1) {
          let redirectUrl = encodeURIComponent(location.origin + '/h5/#/prelogin');
          window.location.href = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='+res.weixinmob_key+'&redirect_uri='+redirectUrl+'&response_type=code&scope=snsapi_base&state=123#wechat_redirect'
        } else {
          Toast.clear()
          this.goOriginLogin()
        }
      })
    }
  }
}
</script>
