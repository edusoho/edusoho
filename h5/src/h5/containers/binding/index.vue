<template>
  <div class="register">
    <e-loading v-if="isLoading" />
    <span class="register-title">绑定手机</span>

    <van-field
      v-model="registerInfo.mobile"
      :border="false"
      :error-message="errorMessage.mobile"
      placeholder="请输入手机号"
      max-length="11"
      @blur="validateMobileOrPsw('mobile')"
      @keyup="validatedChecker()"
    />

    <van-field
      v-model="registerInfo.smsCode"
      :border="false"
      type="text"
      center
      clearable
      max-length="6"
      placeholder="请输入验证码"
    >
      <van-button
        slot="button"
        :disabled="count.codeBtnDisable || !validated.mobile"
        :loading="sendSmsLoading"
        loading-text="发送验证码中..."
        size="small"
        type="primary"
        @click="handleSendSms"
      >
        发送验证码
        <span v-show="count.showCount">({{ count.num }})</span>
      </van-button>
    </van-field>

    <van-button
      :disabled="btnDisable"
      type="primary"
      class="primary-btn mb32"
      :loading="submitLoading"
      loading-text="绑定中..."
      @click="handleSubmit"
      >确认绑定</van-button
    >

    <div class="binding-tip">绑定手机号的三大理由</div>

    <div class="binding-reasons">
      <div class="binding-reasons__item">
        1、网信办规定，互联网注册用户要提供基于移动电话号码等真实身份。
      </div>
      <div class="binding-reasons__item">
        2、第三方登录出现故障是，仍能用手机号顺利登录，课程学习不受影响。
      </div>
      <div class="binding-reasons__item">
        3、即使您忘记了第三方帐号的密码，仍能使用绑定的手机号和密码登录。
      </div>
    </div>
  </div>
</template>
<script>
import activityMixin from '@/mixins/activity';
import redirectMixin from '@/mixins/saveRedirect';
import { mapActions, mapState } from 'vuex';
// eslint-disable-next-line no-unused-vars
import '@/utils/xxtea.js';
import { Toast } from 'vant';
import rulesConfig from '@/utils/rule-config.js';

export default {
  mixins: [activityMixin, redirectMixin],
  data() {
    return {
      registerInfo: {
        mobile: '',
        smsToken: '',
        type: 'register',
      },
      dragKey: 0,
      errorMessage: {
        mobile: '',
      },
      validated: {
        mobile: false,
      },
      count: {
        showCount: false,
        num: 120,
        codeBtnDisable: false,
      },
      submitLoading: false,
      sendSmsLoading: false,
    };
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
    }),
    btnDisable() {
      return !this.registerInfo.mobile || !this.registerInfo.smsCode;
    },
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
        this.errorMessage[type] = '最大输入20个字符';
        return false;
      }

      this.errorMessage[type] = !rule.validator(ele) ? rule.message : '';
    },
    validatedChecker() {
      const mobile = this.registerInfo.mobile;
      const rule = rulesConfig.mobile;

      this.validated.mobile = rule.validator(mobile);
    },
    handleSubmit() {
      const { mobile, smsCode, smsToken } = this.registerInfo;

      this.submitLoading = true;
      this.setMobile({
        query: {
          mobile,
        },
        data: {
          smsCode,
          smsToken,
        },
      })
        .then(res => {
          Toast.success({
            duration: 2000,
            message: '绑定成功',
          });
          this.afterLogin();
        })
        .catch(err => {
          Toast.fail(err.message);
        })
        .finally(() => {
          this.submitLoading = false;
        });
    },
    handleSendSms() {
      this.sendSmsLoading = true;
      this.sendSmsCenter(this.registerInfo)
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
            default:
              Toast.fail(err.message);
              break;
          }
        })
        .finally(() => {
          this.sendSmsLoading = false;
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

<style scoped>
.binding-tip {
  width: 100%;
  margin-bottom: 16px;
  font-size: 18px;
  color: #333;
  font-weight: bold;
}

.binding-reasons {
  padding: 16px;
  background-color: #eaf1fb;
  border-radius: 8px;
}

.binding-reasons .binding-reasons__item:not(:last-child) {
  margin-bottom: 24px;
}

.binding-reasons__item {
  font-size: 14px;
  color: #666;
}
</style>
