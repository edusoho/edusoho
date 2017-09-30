import ajax from '../ajax';

const cashierTradeModule = (api) => {
  return {
    get(options) {
      return ajax(Object.assign({
        url: `${api}/cashier/trades/${options.params.tradeSn}`,
      }, options));
    },

    create() {
      return ajax(Object.assign({
        url: `${api}/cashier/trades`,
        type: 'POST',
      }, options));

    }
  }
};

export default cashierTradeModule;