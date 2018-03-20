import ajax from '../ajax';

const conversationModule = (api) => {
  return {
    search(options) {
      return ajax(Object.assign({
        url: `${api}/conversations`,
        type: 'GET',
      }, options));
    }
  };
};

export default conversationModule;