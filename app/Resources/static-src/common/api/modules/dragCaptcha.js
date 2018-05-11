import ajax from '../ajax';

const dragCaptchaModule = (api) => {
  return {
    get(options) {
      return ajax(Object.assign({
        url: `${api}/drag_captcha`,
        type: 'POST',
      }, options));
    },
    validate(options) {
      return ajax(Object.assign({
        url: `${api}/drag_captcha/${options.params.token}`,
        type: 'GET',
      }, options));
    }
  };
};

export default dragCaptchaModule;