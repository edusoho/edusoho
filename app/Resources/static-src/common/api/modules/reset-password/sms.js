import ajax from '../../ajax';

const resetPasswordSms = (api) => {
  return {
    get(options) {
      return ajax(Object.assign({
        url: `${api}/user/${options.params.mobile}/sms_reset_password`,
        type: 'POST',
      }, options));
    },
    validate(options) {
      return ajax(Object.assign({
        url: `${api}/user/${options.params.mobile}/sms_reset_password/${options.params.smsCode}`,
        type: 'get',
      }, options));
    },
  };
};

export default resetPasswordSms;