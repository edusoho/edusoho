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
      console.log(options);
      return ajax(Object.assign({
        url: `${api}/drag_captcha/${options.params.token}?jigsaw=${options.params.jigsaw}`,
        type: 'GET',
      }, options));
    }
  };
};

export default dragCaptchaModule;