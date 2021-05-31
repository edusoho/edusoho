import ajax from '../ajax';

const fastLoginSmsModule = (api) => {
  return {
    send(options) {
      return ajax(Object.assign({
        url: `${api}/sms_send`,
        type: 'POST'
      }, options));
    }
  };
};

export default fastLoginSmsModule;