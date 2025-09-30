import SceneReport from './scene-report';
import SceneReportComponent from 'common/vue/components/item-bank/scene-report';

jQuery.support.cors = true;

Vue.config.productionTip = false;

Vue.component(SceneReportComponent.name, SceneReportComponent);

new Vue({
  render: createElement => createElement(SceneReport)
}).$mount('#app');

let needJob = $('.js-testpaper-container').data('needJob');
let jobSync = $('.js-testpaper-container').data('jobSync');

//需要Job
if (needJob) {
  if (jobSync) {
    $('.js-data-large-loading').show();
    let internal = setInterval(() => {
      $.get($('.js-data-large-loading-btn').data('checkUrl'), function (resp) {
        if (resp) {
          clearInterval(internal);
          $('.js-data-large-loading').hide();
          $('.js-data-large-finish').show();
          setTimeout(() => {
            $('.js-data-large-finish').hide();
            window.location.reload();
          }, 3000);
        }
      });
    }, 5000);
  } else {
    $('.js-data-large-info').show();
  }
}

$('.js-data-large-loading-btn').on('click', function () {
  $.get($('.js-data-large-loading-btn').data('url'), function (resp) {
    $('.js-data-large-info').hide();
    $('.js-data-large-loading').show();
    let internal = setInterval(() => {
      $.get($('.js-data-large-loading-btn').data('checkUrl'), function (resp) {
        if (resp) {
          clearInterval(internal);
          $('.js-data-large-loading').hide();
          $('.js-data-large-finish').show();
          setTimeout(() => {
            $('.js-data-large-finish').hide();
            window.location.reload();
          }, 3000);

        }
      });
    }, 5000);
  });
})

