<template>
  <div class="register">
    <e-loading v-if="isLoading"></e-loading>
    <span class="register-title">{{ registerType[pathName] }}</span>

      <van-field
        ref="mobile"
        v-model="registerInfo.mobile"
        placeholder="请输入手机号"
        maxLength="11"
        :error-message="errorMessage.mobile"
        @blur="validateMobileOrPsw('mobile')"
        @keyup="validatedChecker()"
      />

      <van-field
        v-model="registerInfo.encrypt_password"
        type="password"
        maxLength="20"
        :error-message="errorMessage.encrypt_password"
        @blur="validateMobileOrPsw('encrypt_password')"
        :placeholder="placeHolder[pathName]"
      />

      <e-drag
        ref="dragComponent"
        v-if="dragEnable"
        :key="dragKey"
        :info="registerInfo"
        @success="handleSmsSuccess"></e-drag>

      <van-field
        v-model="registerInfo.smsCode"
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
          :disabled="count.codeBtnDisable || !validated.mobile"
          @click="clickSmsBtn">
          发送验证码
          <span v-show="count.showCount">({{ count.num }})</span>
          </van-button>
      </van-field>

      <van-button type="default"
        class="primary-btn mb20"
        :disabled="btnDisable"
        @click="handleSubmit">{{ btnType[pathName] }}</van-button>

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
  </div>
</template>
<script>
import EDrag from '@/containers/components/e-drag';
import { mapActions, mapState } from 'vuex';
import XXTEA from '@/utils/xxtea.js';
import { Toast } from 'vant';
import rulesConfig from '@/utils/rule-config.js'

const emptyRegisterInfo = {
  mobile: '',
  dragCaptchaToken: '',
  encrypt_password: '',
  smsCode: '',
  smsToken: '',
  type: 'register'
};
const registerType = {
  binding: '绑定手机',
  register: '注册账号'
}
const btnType = {
  binding: '绑定',
  register: '注册'
}
const placeHolder = {
  binding: '请输入密码',
  register: '请设置密码（5-20位字符）'
}

export default {
  components: {
    EDrag
  },
  data() {
    return {
      registerInfo: emptyRegisterInfo,
      dragEnable: false,
      dragKey: 0,
      submitFlag: true,
      options: [{
        model: 'email'
      }, {
        model: 'mobile'
      }],
      errorMessage: {
        mobile: '',
        encrypt_password: ''
      },
      validated: {
        mobile: false,
        encrypt_password: false
      },
      count: {
        showCount: false,
        num: 120,
        codeBtnDisable: false
      },
      pathName: this.$route.name,
      registerType,
      btnType,
      placeHolder
    }
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading
    }),
    btnDisable() {
      return !(this.registerInfo.mobile
        && this.registerInfo.encrypt_password
        && this.registerInfo.smsCode);
    },
  },
  methods: {
    ...mapActions([
      'addUser',
      'setMobile',
      'sendSmsCenter',
      'userLogin'
    ]),
    validateMobileOrPsw(type = 'mobile') {
      const ele = this.registerInfo[type];
      const rule = rulesConfig[type];

      if (ele.length == 0) {
        this.errorMessage[type] = '';
        return false;
      };

      this.errorMessage[type] = !rule.validator(ele)
        ? rule.message: '';
    },
    validatedChecker() {
      const mobile = this.registerInfo.mobile;
      const rule = rulesConfig['mobile'];

      this.validated.mobile = rule.validator(mobile);
    },
    handleSmsSuccess(token) {
      this.registerInfo.dragCaptchaToken = token;
      this.handleSendSms();
    },
    handleSubmit() {
      const registerInfo = Object.assign({}, this.registerInfo);
      const password = registerInfo.encrypt_password;
      const mobile = registerInfo.mobile;

      if(this.submitFlag) {
        const encrypt = window.XXTEA.encryptToBase64(password, window.location.host);
        registerInfo.encrypt_password = encrypt;
        this.submitFlag = false;
      }

      // 手机绑定
      if (this.pathName === 'binding') {
        this.setMobile({
          query: {
            mobile,
          },
          data: {
            password,
            smsCode: registerInfo.smsCode,
            smsToken: registerInfo.smsToken
          }
        })
        .then(res => {
          Toast.success({
            duration: 2000,
            message: '绑定成功'
          });
          const redirect = decodeURIComponent(this.$route.query.redirect || 'find');
          var jumpToLogin = () => {
            this.$router.replace({ path: redirect });
          }
          setTimeout(jumpToLogin, 2000);
        })
        .catch(err => {
          Toast.fail(err.message);
        });
        return;
      }

      // 手机注册
      this.addUser(registerInfo)
      .then(res => {
        Toast.success({
          duration: 2000,
          message: '注册成功'
        });
        const redirect = decodeURIComponent(this.$route.query.redirect || 'find');
        var jumpToLogin = () => {
          this.$router.replace({ path: redirect });
        }
        setTimeout(jumpToLogin, 2000);
      })
      .then(() => {
        this.userLogin({
          password,
          username: mobile,
        })
      })
      .catch(err => {
        Toast.fail(err.message);
      });
    },
    clickSmsBtn() {
      if (!this.dragEnable) {
        this.dragEnable = true
        return;
      }
      // 验证码组件更新数据
      this.$refs.dragComponent.initDragCaptcha();
    },
    handleSendSms() {
      this.sendSmsCenter(this.registerInfo)
      .then(res => {
        this.registerInfo.smsToken = res.smsToken;
        this.countDown();
      })
      .catch(err => {
        switch(err.code) {
          case 4030301:
          case 4030302:
            this.dragKey ++;
            this.registerInfo.dragCaptchaToken = '';
            this.registerInfo.smsToken = '';
            break;
          case 4030303:
            this.dragEnable = true;
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

