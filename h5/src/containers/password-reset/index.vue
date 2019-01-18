<template>
  <div class="register">
    <e-loading v-if="isLoading"></e-loading>
    <span class='register-title'>找回密码</span>

      <e-drag
        ref="dragComponent"
        :key="dragKey"
        @success="handleSmsSuccess"></e-drag>

      <van-field
        v-model="resetInfo.account"
        placeholder="请输入手机号或邮箱号"
        :error-message="errorMessage.account"
        @blur="validateAccountOrPsw('account')"
        @keyup="validatedChecker()"
      />

      <van-field
        v-show="accountType === 'mobile'"
        v-model="resetInfo.encrypt_password"
        type="password"
        maxLength="20"
        :error-message="errorMessage.encrypt_password"
        @blur="validateAccountOrPsw('encrypt_password')"
        placeholder="请设置密码（5-20位字符）"
      />

      <van-field
        v-show="accountType === 'mobile'"
        v-model="resetInfo.smsCode"
        type="text"
        center
        clearable
        maxLength="6"
        placeholder="请输入验证码"
        >
        <van-button
          slot="button"
          size="small"
          type="primary"
          :disabled="(count.codeBtnDisable || !validated.account)"
          @click="clickSmsBtn">
          发送验证码
          <span v-show="count.showCount">({{ count.num }})</span>
          </van-button>
      </van-field>

      <van-button type="default"
        class="primary-btn mb20"
        :disabled="btnDisable"
        @click="handleSubmit">确认</van-button>
  </div>
</template>

<script>
import Api from '@/api'
import EDrag from '@/containers/components/e-drag';
import { mapState } from 'vuex';
import XXTEA from '@/utils/xxtea.js';
import { Dialog, Toast } from 'vant';
import rulesConfig from '@/utils/rule-config.js'

const emptyresetInfo = {
  account: '',
  accountType: '',
  dragCaptchaToken: '',
  encrypt_password: '',
  smsCode: '',
  smsToken: '',
  type: 'register'
};

export default {
  name: 'password-reset',
  components: {
    EDrag
  },
  data() {
    return {
      resetInfo: emptyresetInfo,
      dragKey: 0,
      errorMessage: {
        account: '',
        encrypt_password: ''
      },
      validated: {
        account: false,
        encrypt_password: false
      },
      count: {
        showCount: false,
        num: 120,
        codeBtnDisable: false
      },
    }
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading
    }),
    btnDisable() {
      return !((this.resetInfo.account
        && this.resetInfo.encrypt_password
        && this.resetInfo.smsCode)
        || this.accountType === 'email');
    },
    accountType() {
      return this.resetInfo['account'].includes('@') ? 'email' : 'mobile';
    },
  },
  methods: {
    validateAccountOrPsw(type = 'account') {
      const ele = this.resetInfo[type];
      const rule = type === 'account' ?
        rulesConfig[this.accountType] : rulesConfig[type]; // 规则：账号／密码

      if (ele.length == 0) {
        this.errorMessage[type] = '';
        return false;
      };

      this.errorMessage[type] = !rule.validator(ele)
        ? rule.message: '';
    },
    validatedChecker() {
      const account = this.resetInfo.account;
      const type = this.accountType;
      const rule = rulesConfig[type];

      this.validated.account = rule.validator(account);
    },
    handleSmsSuccess(token) {
      this.resetInfo.dragCaptchaToken = token;
    },
    handleSubmit() {
      const resetInfo = Object.assign({}, this.resetInfo);
      const password = resetInfo.encrypt_password;
      const account = resetInfo.account;
      const encrypt = window.XXTEA.encryptToBase64(password, window.location.host);

      resetInfo.encrypt_password = encrypt;

      // 邮箱重置
      if (this.accountType === 'email') {
        const dragCaptchaToken = this.resetInfo.dragCaptchaToken;
        Api.resetPasswordByEmail({
          query: { email: account },
          data: { dragCaptchaToken },
        })
        .then(res => {
          Dialog.alert({
            message: '验证链接已发送到\ ' + account,
          })
          .then(() => {
            this.$router.replace({
              name: 'login',
              params: {
                username: account
              },
            });
          });
        })
        .catch(err => {
          switch(err.code) {
            case 4030301:
            case 4030302:
              this.dragKey ++;
              this.resetInfo.dragCaptchaToken = '';
              break;
          }
          Toast.fail(err.message);
        });
        return
      }

      // 手机重置
      Api.resetPasswordByMobile({
        query: { mobile: account },
        data: {
          smsToken: resetInfo.smsToken,
          smsCode: resetInfo.smsCode,
          encrypt_password: resetInfo.encrypt_password,
        }
      })
      .then(res => {
        Dialog.alert({
          message: '密码重置成功',
        })
        .then(() => {
          this.$router.replace({
            name: 'login',
            params: {
              username: account
            },
          });
        })
      })
      .catch(err => {
        Toast.fail(err.message);
      });

    },
    clickSmsBtn() {
      this.handleSendSms();
    },
    handleSendSms() {
      const mobile = this.resetInfo.account;
      const dragCaptchaToken = this.resetInfo.dragCaptchaToken;
      Api.resetPasswordSMS({
        query: { mobile },
        data: { dragCaptchaToken },
      })
      .then(res => {
        this.resetInfo.smsToken = res.smsToken;
        this.countDown();
      })
      .catch(err => {
        switch(err.code) {
          case 4030301:
          case 4030302:
            this.dragKey ++;
            this.resetInfo.dragCaptchaToken = '';
            this.resetInfo.smsToken = '';
            break;
        }
        Toast.fail(err.message);
      });
    },
    // 倒计时
    countDown() {
      this.count.showCount = true;
      this.count.codeBtnDisable = true;
      this.count.num = 120;

      const timer = setInterval(() => {
        if(this.count.num <= 0) {
          this.count.codeBtnDisable = false;
          this.count.showCount = false
          clearInterval(timer);
          return;
        }
        this.count.num--;
      }, 1000);
    }
  }
}
</script>
