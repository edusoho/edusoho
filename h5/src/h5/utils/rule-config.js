import * as messages from './rule-message';

export default {
  phone: {
    message: messages.PHONE_MESSAGE,
    validator(str) {
      const reg = /^1\d{10}$/;
      return reg.test(str);
    },
  },
  mobile: {
    message: messages.PHONE_MESSAGE,
    validator(str) {
      const reg = /^1\d{10}$/;
      return reg.test(str);
    },
  },
  encrypt_password: {
    message: messages.PASSWORD_REGISTER,
    validator(str) {
      if (typeof str !== 'string') {
        return false;
      }

      if (str.length < 8 || str.length > 32) {
        return false;
      }

      const hasLetter = /[a-zA-Z]/.test(str);
      const hasNumber = /[0-9]/.test(str);
      const hasSymbol = /[^a-zA-Z0-9]/.test(str);

      const typeCount = [hasLetter, hasNumber, hasSymbol].filter(Boolean).length;

      return typeCount >= 2;
    },
  },
  email: {
    message: messages.EMAIL_MESSAGE,
    validator(str) {
      const reg = /^([a-zA-Z0-9_.\-+])+@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
      return reg.test(str);
    },
  },
};
