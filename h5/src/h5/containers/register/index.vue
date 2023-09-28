<template>
  <div class="register">
    <e-loading v-if="isLoading" />
    <span class="register-title">{{ $t(registerType[pathName]) }}</span>

    <van-field
      v-model="registerInfo.mobile"
      :border="false"
      :error-message="errorMessage.mobile"
      :placeholder="$t('placeholder.mobileNumber')"
      max-length="11"
      @blur="validateMobileOrPsw('mobile')"
      @keyup="validatedChecker()"
    />

    <van-field
      v-model="registerInfo.encrypt_password"
      :border="false"
      :error-message="errorMessage.encrypt_password"
      :placeholder="$t(placeHolder[pathName])"
      type="password"
      max-length="20"
      @blur="validateMobileOrPsw('encrypt_password')"
    />

    <e-drag
      v-if="dragEnable"
      ref="dragComponent"
      :key="dragKey"
      @success="handleSmsSuccess"
    />

    <van-field
      v-model="registerInfo.smsCode"
      :border="false"
      type="text"
      center
      clearable
      max-length="6"
      :placeholder="$t('placeholder.verificationCode')"
    >
      <van-button
        slot="button"
        :disabled="count.codeBtnDisable || !validated.mobile"
        size="small"
        type="primary"
        @click="clickSmsBtn"
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
      >{{ $t(btnType[pathName]) }}</van-button
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

    <!-- <div class="login-bottom ">
        请详细阅读 <router-link to="/protocol">《用户服务协议》</router-link>
      </div> -->

    <!-- 一期不做 -->
    <!-- <div class="register-social">
        <span>
          <i class="iconfont icon-qq"></i>
          <i class="iconfont icon-weixin1"></i>
          <i class="iconfont icon-weibo"></i>
        </span>
        <div class="line"></div>
      </div> -->

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
import { mapActions, mapState } from 'vuex';
// eslint-disable-next-line no-unused-vars
import XXTEA from '@/utils/xxtea.js';
import { Toast } from 'vant';
import rulesConfig from '@/utils/rule-config.js';
import Api from '@/api';

const registerType = {
  binding: 'title.bindingMobile',
  register: 'title.registerAccount',
};
const btnType = {
  binding: 'btn.binding',
  register: 'btn.register',
};
const placeHolder = {
  binding: 'placeholder.password',
  register: 'placeholder.setPassword',
};

export default {
  components: {
    EDrag,
  },
  mixins: [activityMixin, redirectMixin],
  data() {
    return {
      registerInfo: {
        mobile: '',
        dragCaptchaToken: undefined, // 默认不需要滑动验证
        encrypt_password: '',
        smsCode: '',
        smsToken: '',
        type: 'register',
      },
      dragEnable: false,
      dragKey: 0,
      errorMessage: {
        mobile: '',
        encrypt_password: '',
      },
      validated: {
        mobile: false,
        encrypt_password: false,
      },
      count: {
        showCount: false,
        num: 120,
        codeBtnDisable: false,
      },
      pathName: this.$route.name,
      registerType,
      btnType,
      placeHolder,
      userTerms: false, // 用户协议
      privacyPolicy: false, // 隐私协议
      agreement: false, // 是否勾选
      popUpBottom: false, // 底部弹出层
    };
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
    }),
    btnDisable() {
      return !(
        this.registerInfo.mobile &&
        this.registerInfo.encrypt_password &&
        this.registerInfo.smsCode
      );
    },
  },
  created() {
    this.getPrivacySetting();
  },
  mounted() {
    this.registerInfo.registerVisitId = window._VISITOR_ID;
  },
  methods: {
    ...mapActions(['addUser', 'setMobile', 'sendSmsCenter', 'userLogin']),
    validateMobileOrPsw(type = 'mobile') {
      const ele = this.registerInfo[type];
      const rule = rulesConfig[type];

      if (ele.length == 0) {
        this.errorMessage[type] = '';
        return false;
      }

      if (type === 'encrypt_password' && ele.length > 20) {
        this.errorMessage[type] = this.$t('toast.enterUpTo20Characters');
        return false;
      }

      this.errorMessage[type] = !rule.validator(ele) ? rule.message : '';
    },
    validatedChecker() {
      const mobile = this.registerInfo.mobile;
      const rule = rulesConfig.mobile;

      this.validated.mobile = rule.validator(mobile);
    },
    handleSmsSuccess(token) {
      this.registerInfo.dragCaptchaToken = token;
      this.handleSendSms();
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
      const registerInfo = Object.assign({}, this.registerInfo);
      const password = registerInfo.encrypt_password;
      const mobile = registerInfo.mobile;
      const encrypt = window.XXTEA.encryptToBase64(
        password,
        window.location.host,
      );

      registerInfo.encrypt_password = encrypt;

      // 手机绑定
      if (this.pathName === 'binding') {
        this.setMobile({
          query: {
            mobile,
          },
          data: {
            password,
            smsCode: registerInfo.smsCode,
            smsToken: registerInfo.smsToken,
          },
        })
          .then(res => {
            Toast.success({
              duration: 2000,
              message: this.$t('toast.bindingSuccess'),
            });
            this.afterLogin();
          })
          .catch(err => {
            Toast.fail(err.message);
          });
        return;
      }

      if (
        this.agreement ||
        (this.privacyPolicy === false && this.userTerms === false)
      ) {
        // 手机注册
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

    clickSmsBtn() {
      if (!this.dragEnable) {
        this.handleSendSms();
        return;
      }
      // 验证码组件更新数据
      if (!this.$refs.dragComponent.dragToEnd) {
        Toast(this.$t('toast.pleaseCompleteThePuzzleVerification'));
        return;
      }
      this.$refs.dragComponent.initDragCaptcha();
    },
    handleSendSms() {
      this.sendSmsCenter(this.registerInfo)
        .then(res => {
          this.registerInfo.smsToken = res.smsToken;
          this.countDown();
          this.dragEnable = false;
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
              if (this.dragEnable) {
                Toast.fail(err.message);
              } else {
                this.dragEnable = true;
              }
              break;
            default:
              Toast.fail(err.message);
              break;
          }
        });
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
