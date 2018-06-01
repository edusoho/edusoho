<template>
  <div class="login">
    <span class='login-title'>绑定账号</span>
    <span class='login-hint'>为获取更优质的服务，请绑定您的手机或邮箱账号!</span>
    <img class='login-avatarimg' src="" />
    <van-field v-model="username" 
      class="login-input e-input"
      placeholder="邮箱/手机/用户名"/>

    <van-field v-model="password" 
      type="password"
      class="login-input e-input"
      placeholder="请输入手机号或邮箱" />
    <van-button type="default" class="primary-btn" @click="onSubmit">登录</van-button>
    <router-link to="/register" class='login-account'>创建账号</router-link>
  </div>
</template>
<script>
import { mapActions } from 'vuex';
import { Toast } from 'vant';

export default {
  data() {
    return {
      username: '',
      password: ''
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
        Toast.success('登录成功');
      }).catch(err => {
        Toast.fail('登录异常');
      })
    }
  }
}
</script>
