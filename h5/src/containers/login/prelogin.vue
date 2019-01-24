<template>
  <div class="prelogin">
    <img class="prelogin-img" src="static/images/noLoginEmpty.png"></image>
    <span class="prelogin-text">登录后查看更多信息</span>
    <van-button type="default"
      class="prelogin-btn" @click.native="goLogin">立即登录</van-button>
  </div>
</template>
<script>
import { Toast } from 'vant';
import Api from '@/api'
import * as types from '@/store/mutation-types';
import redirectMixin from '@/mixins/saveRedirect';

export default {
  mixins: [redirectMixin],
  async beforeCreate(){
    let code = location.search.match(/\?code.*&/g);
    if (code) {
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
        this.$store.commit(types.USER_LOGIN, res);
        Toast.clear()
        Toast.success({
          duration: 2000,
          message: '登录成功'
        });
        this.afterLogin();
      }).catch(err => {
        window.location.href = '/login/bind/weixinmob?os=h5&_target_path='
                +
                encodeURIComponent('/h5/#/prelogin?doLogin=1');
      })
    }
  },
  mounted() {
    if (this.$route.query.doLogin) {
      this.goLogin()
    }
  },
  methods: {
    goLogin() {
      this.$router.push({
        name: 'login',
        query: {
          redirect: this.$route.query.redirect || ''
        }
      })
    }
  }
}
</script>
