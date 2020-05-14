import Import from './import';

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}

$(window).on('beforeunload', function () {
  return Translator.trans('admin.block.not_saved_data_hint');
});

new Vue({
  render: createElement => createElement(Import)
}).$mount('#app');