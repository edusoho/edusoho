import App from './App';

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(App)
}).$mount('#show-product-page');