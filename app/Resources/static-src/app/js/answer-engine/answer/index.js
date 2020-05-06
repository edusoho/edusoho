import Vue from 'vue';
import { itemEngine} from 'item-bank-test';
import Answer from './answer';

Vue.use(itemEngine);

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Answer)
}).$mount('#app');