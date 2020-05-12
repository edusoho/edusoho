import Answer from './answer';

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}

new Vue({
  render: createElement => createElement(Answer)
}).$mount('#app');