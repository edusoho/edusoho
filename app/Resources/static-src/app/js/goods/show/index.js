import Goods from './Goods';

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Goods,{
    props: {
      currentUserId: $('#show-product-page').data('currentUserId'),
      targetId: $('#show-product-page').data('targetId'),
      isUserLogin: $('#show-product-page').data('isUserLogin'),
      activityMetas: _toJson($('.js-hidden-activity-metas').html()),
      i18n: _toJson($('.js-hidden-i18n').html()),
    },
  })
}).$mount('#show-product-page');

function _toJson(str) {
  let json = {};
  if (str) {
    json = $.parseJSON(str.replace(/[\r\n\t]/g, ''));
  }
  return json;
}