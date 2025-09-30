import Message from './message';

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Message)
}).$mount('#app');
