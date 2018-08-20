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
      <span class="login-account" @click="jumpRegister">立即注册</span>
    </div>
  </div>
</template>
<script>
import { mapActions } from 'vuex';
import { Toast } from 'vant';
import Api from '@/api'

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
  async created () {
    this.registerSettings = await Api.getSettings({
      query: {
        type: 'register'
      }
    });
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
    },
    jumpRegister() {
      if (this.registerSettings.mode == 'closed') {
        Toast('网校未开启手机注册，请联系管理员');
        return;
      }
      this.$router.push({
        name: 'register'
      })
    }
  }
}
</script>
