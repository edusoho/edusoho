import ajax from '../ajax';
import dragCaptchaModule from './dragCaptcha';

const userModule = (api) => {
  return {
    sendEmailCode(options) {
      return ajax(Object.assign({
        url: `${api}/email_verify_code`,
        type: 'POST'
      }, options));
    }
  };
};

export default userModule;