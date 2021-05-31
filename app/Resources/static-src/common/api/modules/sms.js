import ajax from '../ajax';

const smsModule = (api) => {
  return {
    send(options) {
      return ajax(Object.assign({
        url: `${api}/sms_center`,
        type: 'POST'
      }, options));
    },
    login(options) {
      return ajax(Object.assign({
        url: `${api}/sms_send`,
        type: 'POST'
      }, options));
    }
  };
};

export default smsModule;