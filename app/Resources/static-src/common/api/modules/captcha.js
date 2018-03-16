import ajax from '../ajax';

const captchaModule = (api) => {
  return {
    get(options) {
      return ajax(Object.assign({
        url: `${api}/captcha`,
        type: 'POST',
      }, options));
    },
    validate(options) {
      return ajax(Object.assign({
        url: `${api}/captcha/${options.params.captchaToken}`,
        type: 'GET',
      }, options));
    }
  };
};

export default captchaModule;