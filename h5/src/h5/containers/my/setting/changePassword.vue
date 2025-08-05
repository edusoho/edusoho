<script>
import {Toast} from 'vant';
import Api from '@/api';
import XXTEA from '@/utils/xxtea.js';
import * as types from '@/store/mutation-types';

export default {
  data() {
    return {
      oldPassword: '',
      newPassword: '',
      confirmPassword: '',
      showOldPassword: false,
      showNewPassword: false,
      showConfirmPassword: false,
    }
  },
  methods: {
    toggleOldPasswordVisibility () {
      this.showOldPassword = !this.showOldPassword
    },
    toggleNewPasswordVisibility () {
      this.showNewPassword = !this.showNewPassword
    },
    toggleConfirmPasswordVisibility () {
      this.showConfirmPassword = !this.showConfirmPassword
    },
    confirm() {
      if (this.oldPassword === '') {
        Toast({
          duration: 1000,
          message: '请输入当前密码',
          position: 'bottom',
        });
        return
      }
      if (this.newPassword === '') {
        Toast({
          duration: 1000,
          message: '请输入新密码',
          position: 'bottom',
        });
        return
      }
      if (this.confirmPassword === '') {
        Toast({
          duration: 1000,
          message: '请再次输入新密码',
          position: 'bottom',
        });
        return
      }
      if (this.newPassword !== this.confirmPassword) {
        Toast({
          duration: 1000,
          message: '两次输入的新密码不一致',
          position: 'bottom',
        });
        return
      }
      Api.resetPassword({
        data: {
          oldPassword: this.oldPassword,
          encryptPassword: window.XXTEA.encryptToBase64(
            this.newPassword,
            window.location.host,
          )
        }
      }).then(res => {
        Toast({
          duration: 2000,
          message: '密码修改成功，请重新登录',
        });
        setTimeout(() => {
          this.$store.commit(types.USER_LOGIN, {
            token: '',
            user: {},
          });
          window.localStorage.setItem('mobile_bind_skip', '0');
          this.$router.push({
            name: 'login',
            query: {
              redirect: this.$route.query.redirect || '/'
            }
          });
        }, 2000);
      }).catch(err => {
        Toast({
          duration: 1000,
          message: err.message,
          position: 'bottom',
        });
      });
    },
  }
}
</script>

<template>
  <div class="change-password-container">
    <van-field
      v-model.trim="oldPassword"
      :border="false"
      :type="showOldPassword ? 'text' : 'password'"
      class="login-input"
      :placeholder="$t('placeholder.oldPassword')"
      style="margin-top: 0; padding-top: 0"
    >
      <template #button>
        <img v-if="showOldPassword" src="static/images/open-eye.svg" alt="" @click="toggleOldPasswordVisibility">
        <img v-else src="static/images/close-eye.svg" alt="" @click="toggleOldPasswordVisibility">
      </template>
    </van-field>
    <van-field
      v-model.trim="newPassword"
      :border="false"
      :type="showNewPassword ? 'text' : 'password'"
      class="login-input"
      :placeholder="$t('placeholder.newPassword')"
    >
      <template #button>
        <img v-if="showNewPassword" src="static/images/open-eye.svg" alt="" @click="toggleNewPasswordVisibility">
        <img v-else src="static/images/close-eye.svg" alt="" @click="toggleNewPasswordVisibility">
      </template>
    </van-field>
    <van-field
      v-model.trim="confirmPassword"
      :border="false"
      :type="showConfirmPassword ? 'text' : 'password'"
      class="login-input"
      :placeholder="$t('placeholder.confirmPassword')"
      style="padding-bottom: 18px"
    >
      <template #button>
        <img v-if="showConfirmPassword" src="static/images/open-eye.svg" alt="" @click="toggleConfirmPasswordVisibility">
        <img v-else src="static/images/close-eye.svg" alt="" @click="toggleConfirmPasswordVisibility">
      </template>
    </van-field>
    <div class="password-tip">{{ $t('tips.password') }}</div>
    <button class="save-button" @click="confirm">{{ $t('btn.confirm') }}</button>
  </div>
</template>
