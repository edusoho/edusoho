// eslint-disable-next-line import/extensions
import rulesConfig from '@/utils/rule-config.js';
import { Toast } from 'vant';

export default {
  data() {
    return {
      count: {
        showCount: false,
        num: 60,
        codeBtnDisable: false
      }
    };
  },
  methods: {
    // 校验手机号
    validateMobileOrPsw(type = 'mobile') {
      const ele = this.userinfo[type];
      const rule = rulesConfig[type];
      if (ele.length === 0) {
        this.errorMessage[type] = '';
      }
      this.errorMessage[type] = !rule.validator(ele)
        ? rule.message : '';
    },
    validatedChecker() {
      if (this.userinfo.mobile.length > 11) {
        this.userinfo.mobile = this.userinfo.mobile.substring(0, 11);
      }
      const mobile = this.userinfo.mobile;
      const rule = rulesConfig.mobile;
      this.validated.mobile = rule.validator(mobile);
    },
    countDown() {
      // 验证码自动聚焦
      this.$nextTick(() => {
        this.$refs.smsCode.$refs.input.focus();
      });

      this.count.showCount = true;
      this.count.codeBtnDisable = true;
      this.count.num = 60;

      const timer = setInterval(() => {
        if (this.count.num <= 0) {
          this.count.codeBtnDisable = false;
          this.count.showCount = false;
          clearInterval(timer);
          return;
        }
        this.count.num -= 1;
      }, 1000);
    },
    handleSubmit(cb, cb2 = undefined) {
      if (this.btnDisable) {
        return;
      }
      this.fastLogin({
        mobile: this.userinfo.mobile,
        smsToken: this.userinfo.smsToken,
        smsCode: this.userinfo.smsCode,
        loginType: 'sms',
        client: 'h5'
      })
        .then(res => cb(res))
        .catch(err => {
          if (cb2) {
            cb2(err.message);
          }
          Toast.fail(err.message);
        });
    },
    handleSendSms() {
      this.sendSmsSend(this.userinfo)
        .then(res => {
          this.userinfo.smsToken = res.smsToken;
          this.countDown();
          this.dragEnable = false;
          this.userinfo.dragCaptchaToken = '';
        })
        .catch(err => {
          switch (err.code) {
            case 4030301:
            case 4030302:
              this.dragKey += 1;
              this.userinfo.dragCaptchaToken = '';
              this.userinfo.smsToken = '';
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
    clickSmsBtn() {
      if (this.count.codeBtnDisable || !this.validated.mobile) {
        return;
      }
      if (!this.dragEnable) {
        this.handleSendSms();
        return;
      }
      // 验证码组件更新数据
      if (!this.$refs.dragComponent.dragToEnd) {
        Toast('请先完成拼图验证');
        return;
      }
      this.$refs.dragComponent.initDragCaptcha();
    }
  }
};
