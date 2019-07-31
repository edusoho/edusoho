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
      const rule = rulesConfig.mobile;
      this.validated.mobile = rule.validator(mobile);
    }
  }
};
