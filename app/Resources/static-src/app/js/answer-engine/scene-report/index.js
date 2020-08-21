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