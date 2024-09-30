import Review from './review';
import ItemReview from 'common/vue/components/item-bank/item-review';

jQuery.support.cors = true;

Vue.config.productionTip = false;

Vue.component(ItemReview.name, ItemReview);

new Vue({
  render: createElement => createElement(Review)
}).$mount('#app');