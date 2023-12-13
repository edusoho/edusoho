import Index from './index.vue';
import Vue from 'common/vue';


Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Index)
}).$mount('#classroomClosed');