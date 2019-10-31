<template>
  <div class="login">
    <span class="login-title">手机快捷登录</span>
    <span class="login-des">新用户将为您自动注册</span>
    <van-field
        v-model="userinfo.mobile"
        placeholder="请输入手机号"
        maxLength="11"
        type="number"
        :border=false
        class="login-input e-input"
        clearable
        :error-message="errorMessage.mobile"
        @blur="validateMobileOrPsw('mobile')"
        @keyup="validatedChecker()"
    />

    <e-drag
        ref="dragComponent"
        v-if="dragEnable"
        limitType="sms_login"
        :key="dragKey"
        @success="handleSmsSuccess"></e-drag>

    <van-field
        v-model="userinfo.smsCode"
        ref="smsCode"
        type="number"
        :border=false
        center
        clearable
        maxLength="6"
        class="login-input e-input"
        placeholder="请输入验证码"
    >
      <van-button
          slot="button"
          size="small"
          type="primary"
          :disabled="count.codeBtnDisable || !validated.mobile"
          @click="clickSmsBtn">
        发送验证码
        <span v-show="count.showCount">({{ count.num }})</span>
      </van-button>
    </van-field>
    <van-button type="default" class="primary-btn mb20" @click="handleSubmit(handleSubmitSuccess)" :disabled="btnDisable">登录</van-button>
    <div class="login-bottom text-center">
      <div class="login-agree" v-if="userTerms || privacyPolicy">
        <van-checkbox v-model="agreement" @click="checkAgree" checked-color="#408ffb" :icon-size="16"></van-checkbox>
        我已阅读并同意<i @click="lookPrivacyPolicy" v-if="userTerms">《用户服务》</i><span v-if="userTerms && privacyPolicy">和</span><span
          v-if="privacyPolicy">《<i @click="lookPrivacyPolicy">隐私协议</i>》</span>
      </div>
      <div class="login-change" @click="changeLogin">
        <img src="static/images/login_change.png" class="login_change-icon"/>切换账号密码登录
      </div>
    </div>
  </div>
</template>
<script>
  import EDrag from '@/containers/components/e-drag';
  import rulesConfig from '@/utils/rule-config.js';
  import XXTEA from '@/utils/xxtea.js';
  import Api from '@/api';
  import activityMixin from '@/mixins/activity';
  import redirectMixin from '@/mixins/saveRedirect';
  import fastLoginMixin from '@/mixins/fastLogin';
  import { mapActions, mapState } from 'vuex';

  export default {
    name: 'fast-login',
    mixins: [activityMixin, redirectMixin, fastLoginMixin],
    components: {
      EDrag
    },
    data() {
      return {
        userinfo: {
          mobile: '',
          dragCaptchaToken: undefined, // 默认不需要滑动验证,图片验证码token
          smsCode: '',//验证码
          smsToken: '',//验证码token
          type: 'sms_login',
        },
        userTerms: false,//用户协议
        privacyPolicy: false,//隐私协议
        registerSettings: null,
        agreement: true,
        dragEnable: false,
        dragKey: 0,
        errorMessage: {
          mobile: '',
        },
        validated: {
          mobile: false,
        },
      };
    },
    computed: {
      btnDisable() {
        return !(this.userinfo.mobile
          && this.userinfo.smsCode
          && this.agreement);
      },
    },
    async created() {
      if (this.$store.state.token) {
        Toast.loading({
          message: '请稍后'
        });
        this.afterLogin();
        return;
      }
      this.getPrivacySetting();
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
              this.userTerms = true;
            }
            if (res.auth.privacy_policy_enabled) {
              this.privacyPolicy = true;
            }
          })
          .catch(err => {
            Toast.fail(err.message);
          });
      },
      //获取隐私政策
      lookPrivacyPolicy() {
        window.location.href = window.location.origin + '/mapi_v2/School/getPrivacyPolicy';
      },
      //校验成功
      handleSmsSuccess(token) {
        this.userinfo.dragCaptchaToken = token;
        this.handleSendSms();
      },
      //登录
      handleSubmitSuccess() {
        this.afterLogin();
      },

      //同意协议
      checkAgree() {
        this.agreement = !this.agreement;
      },
      changeLogin() {
        this.$router.push({
          name: 'login',
        });
      }
    }
  };
</script>
