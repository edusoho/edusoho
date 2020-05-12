import Review from './review';

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}

new Vue({
  render: createElement => createElement(Review)
}).$mount('#app');