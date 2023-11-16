import Goods from './Goods';
import { Input } from 'ant-design-vue';
import Vue from 'common/vue';


Vue.config.productionTip = false;
Vue.use(Input);
Vue.filter('trans', function (value, params) {
  if (!value) return '';
  return Translator.trans(value, params);
});

new Vue({
  render: createElement => createElement(Goods,{
    props: {
      goods: $('#show-product-page').data('goods'),
      componentsData: $('#show-product-page').data('componentsData'),
      currentUserId: $('#show-product-page').data('currentUserId'),
      targetId: $('#show-product-page').data('targetId'),
      isUserLogin: $('#show-product-page').data('isUserLogin'),
      currentUrl: $('#show-product-page').data('currentUrl'),
      activityMetas: _toJson($('.js-hidden-activity-metas').html()),
      i18n: _toJson($('.js-hidden-i18n').html()),
      goodsSetting: $('#show-product-page').data('goodsSetting'),
      timestamp: $('#js-hidden-current-timestamp').html(),
      drpRecruitSwitch: $('#show-product-page').data('drpRecruitSwitch'),
      ugcReviewSetting: $('#show-product-page').data('ugcReviewSetting'),
      vipEnabled: $('#show-product-page').data('vipEnabled'),
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

$(document).on('click', '.js-handleCoursePage', function (event) {
  event.preventDefault();
  window.location.href = '/course/closed?type=course'
});

$(document).on('click', '.js-handleClassroomPage', function (event) {
  event.preventDefault();
  window.location.href = '/course/closed?type=classroom'
});