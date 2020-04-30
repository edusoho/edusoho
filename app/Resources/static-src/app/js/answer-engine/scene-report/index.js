import Vue from 'vue';
import itemBank from 'item-bank-test';
import SceneReport from './scene-report';

Vue.use(itemBank);

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(SceneReport)
}).$mount('#app');