<template>
  <div class="register">
    <e-loading v-if="isLoading" />
    <span class="register-title">{{ $t('title.registerAccount') }}</span>

    <div class="flex justify-center text-16 mt-50">
      <div v-if="showRegisterModeTabs" class="p-10 mr-40" :class="{'border-b border-blue-500 font-medium': registerType === 'mobile'}" @click="registerType = 'mobile'">手机号注册</div>
      <div v-if="showRegisterModeTabs" class="p-10" :class="{'border-b border-blue-500 font-medium': registerType === 'email'}" @click="registerType = 'email'">邮箱号注册</div>
    </div>

    <van-field
      v-if="registerType === 'mobile'"
      v-model="registerInfo.mobile"
      :border="false"
      :error-message="errorMessage.mobile"
      :placeholder="$t('placeholder.mobileNumber')"
      max-length="11"
      @blur="validateMobileOrPswOrEmail('mobile')"
      @keyup="validatedChecker()"
    />

    <van-field
      v-if="registerType === 'email'"
      v-model="registerInfo.email"
      :border="false"
      :error-message="errorMessage.email"
      :placeholder="$t('placeholder.emailNumber')"
      @blur="validateMobileOrPswOrEmail('email')"
      @keyup="validatedChecker()"
    />

    <van-field
      v-model="registerInfo.encrypt_password"
      :border="false"
      :error-message="errorMessage.encrypt_password"
      :placeholder="$t('placeholder.setPassword')"
      :type="showPassword ? 'text' : 'password'"
      max-length="20"
      @blur="validateMobileOrPswOrEmail('encrypt_password')"
    >
      <template #button>
        <img v-if="showPassword" src="static/images/open-eye.svg" alt="" @click="togglePasswordVisibility">
        <img v-else src="static/images/close-eye.svg" alt="" @click="togglePasswordVisibility">
      </template>
    </van-field>
    <div v-if="showPasswordTip" class="password-tip">请设置8-32位包含字母大小写、数字、符号四种字符组合成的密码</div>

    <e-drag
      ref="dragComponent"
      :key="dragKey"
      @success="handleDragSuccess"
    />

    <van-field
      v-model="registerInfo.code"
      :border="false"
      type="text"
      center
      clearable
      max-length="6"
      :placeholder="$t('placeholder.verificationCode')"
      style="margin-top: 0"
    >
      <van-button
        slot="button"
        :disabled="registerInfo.dragCaptchaToken || count.codeBtnDisable || !validated.mobile && !validated.email"
        size="small"
        type="primary"
        @click="clickCodeBtn"
      >
        {{ $t('btn.sendCode') }}
        <span v-show="count.showCount">({{ count.num }})</span>
      </van-button>
    </van-field>

    <van-button
      :disabled="btnDisable"
      type="default"
      class="primary-btn mb20"
      @click="handleSubmit"
      >{{ $t('btn.register') }}</van-button
    >

    <div v-if="userTerms || privacyPolicy" class="login-agree">
      <van-checkbox
        v-model="agreement"
        :icon-size="16"
        checked-color="#408ffb"
      />
      {{ $t('tips.iHaveReadAndAgreeToThe') }}
      <i v-if="userTerms" @click="lookUserTerms"
        >《{{ $t('btn.userServiceAgreement') }}》</i
      >
      <span v-if="userTerms && privacyPolicy">{{ $t('tips.and') }}</span>
      <span v-if="privacyPolicy">
        <i @click="lookPrivacyPolicy">《{{ $t('btn.privacyAgreemen') }}》</i>
      </span>
    </div>

    <van-popup
      v-model="popUpBottom"
      class="login-pop"
      position="bottom"
      round
      :style="{ height: '30%' }"
    >
      <div class="login-pop-title">{{ $t('btn.PleaseReadAgreeAndTerms') }}</div>
      <div v-if="userTerms || privacyPolicy" class="login-agree">
        <i v-if="userTerms" @click="lookPrivacyPolicy"
          >《{{ $t('btn.userServiceAgreement') }}》</i
        >
        <span v-if="privacyPolicy">
          <i @click="lookPrivacyPolicy">《{{ $t('btn.privacyAgreemen') }}》</i>
        </span>
      </div>
      <van-button
        :disabled="btnDisable"
        type="info"
        class="primary-btn mb20 login-pop-btn"
        @click="registerSign"
        >{{ $t('btn.agreeAndRegister') }}</van-button
      >
    </van-popup>
  </div>
</template>
<script>
import activityMixin from '@/mixins/activity';
import redirectMixin from '@/mixins/saveRedirect';
import EDrag from '&/components/e-drag';
import {mapActions, mapState} from 'vuex';
// eslint-disable-next-line no-unused-vars
import {Toast} from 'vant';
import rulesConfig from '@/utils/rule-config.js';
import Api from '@/api';

export default {
  components: {
    EDrag,
  },
  mixins: [activityMixin, redirectMixin],
  data() {
    return {
      registerInfo: {
        email: '',
        mobile: '',
        dragCaptchaToken: undefined,
        encrypt_password: '',
        code: '',
        smsToken: '',
        type: 'register',
      },
      showPassword: false,
      showPasswordTip: true,
      dragKey: 0,
      errorMessage: {
        email: '',
        mobile: '',
        encrypt_password: '',
      },
      validated: {
        email: false,
        mobile: false,
        encrypt_password: false,
      },
      count: {
        showCount: false,
        num: 120,
        codeBtnDisable: false,
      },
      userTerms: false, // 用户协议
      privacyPolicy: false, // 隐私协议
      agreement: false, // 是否勾选
      popUpBottom: false, // 底部弹出层
      registerType: '',
      registerMode: '',
    };
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
    }),
    btnDisable() {
      if (this.registerType === 'mobile') {
        return !(
          this.registerInfo.mobile &&
          this.registerInfo.encrypt_password &&
          this.registerInfo.code
        );
      } else if (this.registerType === 'email') {
        return !(
          this.registerInfo.email &&
          this.registerInfo.encrypt_password &&
          this.registerInfo.code
        );
      }
    },
    showRegisterModeTabs() {
      return this.registerMode === 'email_or_mobile';
    },
  },
  created() {
    this.getPrivacySetting();
  },
  mounted() {
    this.registerInfo.registerVisitId = window._VISITOR_ID;
    this.getRegisterSettings();
  },
  methods: {
    ...mapActions(['addUser', 'setMobile', 'sendSmsCenter', 'userLogin']),
    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
    },
    validateMobileOrPswOrEmail(type = 'mobile') {
      const ele = this.registerInfo[type];
      const rule = rulesConfig[type];

      if (ele.length == 0) {
        this.errorMessage[type] = '';
        return false;
      }

      this.showPasswordTip = rule.validator(ele);
      this.errorMessage[type] = !rule.validator(ele) ? rule.message : '';
    },
    validatedChecker(type = 'mobile') {
      const ele = this.registerInfo[type];
      const rule = rulesConfig[type];

      this.validated[type] = rule.validator(ele);
    },
    handleDragSuccess(token) {
      this.registerInfo.dragCaptchaToken = token;
    },
    async getRegisterSettings() {
      await Api.settingsRegister({})
        .then(res => {
          this.registerMode = res.mode;
          if (this.registerMode === 'mobile') {
            this.registerType = 'mobile'
          } else if (this.registerMode === 'email') {
            this.registerType = 'email'
          } else if (this.registerMode === 'email_or_mobile') {
            this.registerType = 'mobile'
          }
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
    async getPrivacySetting() {
      await Api.getSettings({
        query: {
          type: 'user',
        },
      })
        .then(res => {
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
    // 获取隐私政策
    lookPrivacyPolicy() {
      window.location.href =
        window.location.origin + '/mapi_v2/School/getPrivacyPolicy';
    },
    // 获取服务条款
    lookUserTerms() {
      window.location.href =
        window.location.origin + '/mapi_v2/School/getUserterms';
    },
    handleSubmit() {
      if (this.registerType === 'mobile') {
        const {email, mobile, code, ...reset} = this.registerInfo;
        const registerInfo = Object.assign({}, { ...reset, mobile, smsCode: code});
        const password = registerInfo.encrypt_password;
        registerInfo.encrypt_password = window.XXTEA.encryptToBase64(
          password,
          window.location.host,
        );

        if (this.agreement || (this.privacyPolicy === false && this.userTerms === false)) {
          this.addUser(registerInfo)
            .then(res => {
              Toast.success({
                duration: 2000,
                message: this.$t('toast.registrationSuccess'),
              });
              this.afterLogin();
            })
            .then(() => {
              this.userLogin({
                password,
                username: mobile,
              });
            })
            .catch(err => {
              Toast.fail(err.message);
            });
          return;
        }
        this.popUpBottom = true;
      } else if (this.registerType === 'email') {

      }
    },
    registerSign() {
      const registerInfo = Object.assign({}, this.registerInfo);
      const password = registerInfo.encrypt_password;
      const mobile = registerInfo.mobile;
      const encrypt = window.XXTEA.encryptToBase64(
        password,
        window.location.host,
      );

      registerInfo.encrypt_password = encrypt;

      // 手机注册
      this.addUser(registerInfo)
        .then(res => {
          this.agreement = true;
          this.popUpBottom = false;
          Toast.success({
            duration: 2000,
            message: this.$t('toast.registrationSuccess'),
          });
          this.afterLogin();
        })
        .then(() => {
          this.userLogin({
            password,
            username: mobile,
          });
        })
        .catch(err => {
          Toast.fail(err.message);
          this.popUpBottom = false;
        });
    },

    clickCodeBtn() {
      if (!this.registerInfo.dragCaptchaToken) return;
      if (this.registerType === 'mobile') {
        this.handleSendSms();
      } else if (this.registerType === 'email') {
        this.handleSendEmail();
      }
      // 验证码组件更新数据
      // if (!this.$refs.dragComponent.dragToEnd) {
      //   Toast(this.$t('toast.pleaseCompleteThePuzzleVerification'));
      //   return;
      // }
      // this.$refs.dragComponent.initDragCaptcha();
    },
    handleSendSms() {
      const {mobile, email, code, ...reset} = this.registerInfo;
      this.sendSmsCenter({smsCode: code, mobile, ...reset})
        .then(res => {
          this.registerInfo.smsToken = res.smsToken;
          this.countDown();
        })
        .catch(err => {
          switch (err.code) {
            case 4030301:
            case 4030302:
              this.dragKey++;
              this.registerInfo.dragCaptchaToken = '';
              this.registerInfo.smsToken = '';
              Toast.fail(err.message);
              break;
            case 4030303:
              Toast.fail(err.message);
              break;
            default:
              Toast.fail(err.message);
              break;
          }
        });
    },
    handleSendEmail() {
      const {email, ...reset} = this.registerInfo;
      const params = {
        email: email,
        dragCaptchaToken: this.registerInfo.dragCaptchaToken
      }
      this.sendEmailCenter(params)
        .then(res => {
          this.countDown();
        })
        .catch(err => {
          Toast.fail(err.message);
        })
    },
    // 倒计时
    countDown() {
      this.count.showCount = true;
      this.count.codeBtnDisable = true;
      this.count.num = 120;

      const timer = setInterval(() => {
        if (this.count.num <= 0) {
          this.count.codeBtnDisable = false;
          this.count.showCount = false;
          clearInterval(timer);
          return;
        }
        this.count.num--;
      }, 1000);
    },
  },
};
</script>

<style scoped>
.password-tip {
  font-size: 12px;
  line-height: 24px;
  color: rgba(0, 0, 0, 0.45);
  padding: 0 16px;
}
</style>
