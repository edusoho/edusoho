// import Vue from 'vue';
// import { sceneReport } from 'item-bank-test';
import SceneReport from './scene-report';

// Vue.use(sceneReport);

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(SceneReport)
}).$mount('#app');