<template>
  <div class="login">
    <span class='login-title'>登录账号</span>
    <img class='login-avatarimg' src="" />
    <van-field v-model="username"
      class="login-input e-input"
      placeholder="邮箱/手机/用户名"/>

    <van-field v-model="password"
      type="password"
      class="login-input e-input"
      :error-message="errorMessage.password"
      placeholder="请输入密码" />
    <van-button type="default" class="primary-btn mb20" @click="onSubmit" :disabled="btnDisable">登录</van-button>
    <div class="login-bottom">
      <!-- <router-link to="/register" class='login-account'>找回密码</router-link> -->
      还没有注册帐号？
      <router-link to="/register" class='login-account'>立即注册</router-link>
    </div>
  </div>
</template>
<script>
import { mapActions } from 'vuex';
import { Toast } from 'vant';

export default {
  data() {
    return {
      username: '',
      password: '',
      errorMessage: {
        password: ''
      }
    }
  },
  computed: {
    btnDisable() {
      return !(this.username && this.password);
    }
  },
  methods: {
    ...mapActions([
      'userLogin'
    ]),
    onSubmit() {
      this.userLogin({
        username: this.username,
        password: this.password
      }).then(res => {
        Toast.success({
          duration: 2000,
          message: '登录成功'
        });
        const redirect = decodeURIComponent(this.$route.query.redirect || 'find');
        var jumpAction = () => {
          this.$router.push({name: redirect});
        }
        setTimeout(jumpAction, 2000);
      }).catch(err => {
        Toast.fail(err.message);
      })
    }
  }
}
</script>
