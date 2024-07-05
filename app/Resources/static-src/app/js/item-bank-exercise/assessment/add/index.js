import AddTestPaper from './AddTestPaper.vue';
import Vue from 'common/vue';

new Vue({
  render: createElement => createElement(AddTestPaper, {props: {itemBankId: 1}})
}).$mount('#addTestPaper');
