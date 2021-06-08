import Vue from 'common/vue';
import Router from 'vue-router';
import routes from 'app/vue/router/teach/multi_class/index.js';
import zhCN from '@codeages/design-vue/lib/locale-provider/zh_CN';
import { ConfigProvider } from '@codeages/design-vue';
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const router = new Router({
  mode: 'hash',
  routes
})

window.CKEDITOR_BASEPATH = app.basePath + '/static-dist/libs/es-ckeditor/';

new Vue({
  el: '#app',
  router,
  components: {
    ConfigProvider,
    AntConfigProvider
  },
  data: {
    locale: zhCN
  },
  template: `<ant-config-provider />`
});

