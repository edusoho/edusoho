// eslint-disable-next-line import/extensions
import rulesConfig from '@/utils/rule-config.js';

export default {
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
      const mobile = this.userinfo.mobile;
      if(this.userinfo.mobile.length>11){
        this.userinfo.mobile=this.userinfo.mobile.substring(0,11)
      }
      const rule = rulesConfig.mobile;
      this.validated.mobile = rule.validator(mobile);
    }
  }
};
