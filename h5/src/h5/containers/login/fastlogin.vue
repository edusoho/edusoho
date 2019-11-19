<template>
  <div class="login">
    <span class="login-title">手机快捷登录</span>
    <span class="login-des">新用户将为您自动注册</span>
    <van-field
      v-model="userinfo.mobile"
      :border="false"
      :error-message="errorMessage.mobile"
      placeholder="请输入手机号"
      max-length="11"
      type="number"
      class="login-input e-input"
      clearable
      @blur="validateMobileOrPsw('mobile')"
      @keyup="validatedChecker()"
    />

    <e-drag
      v-if="dragEnable"
      ref="dragComponent"
      :key="dragKey"
      limit-type="sms_login"
      @success="handleSmsSuccess"/>

    <van-field
      ref="smsCode"
      v-model="userinfo.smsCode"
      :border="false"
      type="number"
      center
      clearable
      max-length="6"
      class="login-input e-input"
      placeholder="请输入验证码"
    >
      <van-button
        slot="button"
        :disabled="count.codeBtnDisable || !validated.mobile"
        size="small"
        type="primary"
        @click="clickSmsBtn">
        发送验证码
        <span v-show="count.showCount">({{ count.num }})</span>
      </van-button>
    </van-field>
    <van-button :disabled="btnDisable" type="default" class="primary-btn mb20" @click="handleSubmit(handleSubmitSuccess)">登录</van-button>
    <div class="login-bottom text-center">
      <div v-if="userTerms || privacyPolicy" class="login-agree">
        <van-checkbox v-model="agreement" :icon-size="16" checked-color="#408ffb" @click="checkAgree"/>
        我已阅读并同意<i v-if="userTerms" @click="lookPrivacyPolicy">《用户服务》</i><span v-if="userTerms && privacyPolicy">和</span><span
          v-if="privacyPolicy">《<i @click="lookPrivacyPolicy">隐私协议</i>》</span>
      </div>
      <div class="login-change" @click="changeLogin">
        <img src="static/images/login_change.png" class="login_change-icon">切换账号密码登录
      </div>
    </div>
  </div>
</template>
<script>
import EDrag from '&/components/e-drag'
import rulesConfig from '@/utils/rule-config.js'
import XXTEA from '@/utils/xxtea.js'
import Api from '@/api'
import activityMixin from '@/mixins/activity'
import redirectMixin from '@/mixins/saveRedirect'
import fastLoginMixin from '@/mixins/fastLogin'
import { mapActions, mapState } from 'vuex'

export default {
  name: 'FastLogin',
  components: {
    EDrag
  },
  mixins: [activityMixin, redirectMixin, fastLoginMixin],
  data() {
    return {
      userinfo: {
        mobile: '',
        dragCaptchaToken: undefined, // 默认不需要滑动验证,图片验证码token
        smsCode: '', // 验证码
        smsToken: '', // 验证码token
        type: 'sms_login'
      },
      userTerms: false, // 用户协议
      privacyPolicy: false, // 隐私协议
      registerSettings: null,
      agreement: true,
      dragEnable: false,
      dragKey: 0,
      errorMessage: {
        mobile: ''
      },
      validated: {
        mobile: false
      }
    }
  },
  computed: {
    btnDisable() {
      return !(this.userinfo.mobile &&
          this.userinfo.smsCode &&
          this.agreement)
    }
  },
  async created() {
    if (this.$store.state.token) {
      Toast.loading({
        message: '请稍后'
      })
      this.afterLogin()
      return
    }
    this.getPrivacySetting()
  },
  methods: {
    ...mapActions([
      'addUser',
      'setMobile',
      'sendSmsSend',
      'fastLogin'
    ]),
    async getPrivacySetting() {
      this.registerSettings = await Api.getSettings({
        query: {
          type: 'user'
        }
      })
        .then((res) => {
          if (res.auth.user_terms_enabled) {
            this.userTerms = true
          }
          if (res.auth.privacy_policy_enabled) {
            this.privacyPolicy = true
          }
        })
        .catch(err => {
          Toast.fail(err.message)
        })
    },
    // 获取隐私政策
    lookPrivacyPolicy() {
      window.location.href = window.location.origin + '/mapi_v2/School/getPrivacyPolicy'
    },
    // 校验成功
    handleSmsSuccess(token) {
      this.userinfo.dragCaptchaToken = token
      this.handleSendSms()
    },
    // 登录
    handleSubmitSuccess() {
      this.afterLogin()
    },

    // 同意协议
    checkAgree() {
      this.agreement = !this.agreement
    },
    changeLogin() {
      this.$router.push({
        name: 'login'
      })
    }
  }
}
</script>
