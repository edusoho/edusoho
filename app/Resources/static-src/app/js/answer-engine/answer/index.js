// import Vue from 'vue';
// import { itemEngine, inspectionControl} from 'item-bank-test';
import Answer from './answer';

// Vue.use(itemEngine);
// Vue.use(inspectionControl);

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Answer)
}).$mount('#app');