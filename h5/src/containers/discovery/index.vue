<template>
  <div class="login">
    <span class='login-title'>确认账号</span>
    <van-field v-model="username"
      class="login-input e-input"
      placeholder="请输入邮箱/手机/用户名"/>
    <van-field v-if="faceRegistered" v-model="password"
      type="password"
      class="login-input e-input"
      :error-message="errorMessage.password"
      placeholder="请输入密码" />
    <van-button type="default" class="primary-btn mb20">下一步</van-button>
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
      faceRegistered: 0,
      errorMessage: {
        password: ''
      }
    }
  },
  methods: {
    ...mapActions([
      'userLogin'
    ]),
  }
}
</script>
