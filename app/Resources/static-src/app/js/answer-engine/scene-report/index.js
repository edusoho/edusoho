import SceneReport from './scene-report';

jQuery.support.cors = true;

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}

new Vue({
  render: createElement => createElement(SceneReport)
}).$mount('#app');

let needJob = $('.js-testpaper-container').data('needJob');
let jobSync = $('.js-testpaper-container').data('jobSync');

if (needJob) {
  if (jobSync) {
    $('.js-data-large-info').show();
  } else {
    $('.js-data-large-loading').show();
  }
}
