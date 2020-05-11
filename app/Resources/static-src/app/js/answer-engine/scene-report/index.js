// import Vue from 'vue';
// import { sceneReport } from 'item-bank-test';
import SceneReport from './scene-report';

// Vue.use(sceneReport);

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}


new Vue({
  render: createElement => createElement(SceneReport)
}).$mount('#app');