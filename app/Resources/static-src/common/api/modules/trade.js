import ajax from '../ajax';

const tradeModule = (api) => {
  return {
    get(options) {
      return ajax(Object.assign({
        url: `${api}/trades/${options.params.tradeSn}`,
      }, options));
    },

    create(options) {
      return ajax(Object.assign({
        url: `${api}/trades`,
        type: 'POST',
      }, options));

    }
  };
};

export default tradeModule;