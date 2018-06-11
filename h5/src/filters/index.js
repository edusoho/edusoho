const filters = [
  {
    name: 'toMoney',
    handler(value) {
      return isNaN(Number(value)) ? '0.00' : (Number(value) / 100).toFixed(2);
    }
  }
];

export default {
  install(Vue) {
    filters.map(item => {
      Vue.filter(item.name, item.handler);

      return item;
    });
  }
};
