import Index from './index.vue';
import Vue from 'common/vue';


Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}

new Vue({
  render: createElement => createElement(Index)
}).$mount('#classroomClosed');