import Vue from 'common/vue';
import Router from 'vue-router';
import routes from 'app/vue/router/teach/multi_class/index.js';
import zhCN from 'ant-design-vue/lib/locale-provider/zh_CN';
import { ConfigProvider } from 'ant-design-vue';

const router = new Router({
  mode: 'hash',
  routes
})

window.CKEDITOR_BASEPATH = app.basePath + '/static-dist/libs/es-ckeditor/';

new Vue({
  el: '#app',
  router,
  components: {
    ConfigProvider
  },
  data: {
    locale: zhCN
  },
  template: `
    <config-provider :locale="locale">
      <keep-alive v-if="$route.meta.keepAlive">
        <router-view></router-view>
      </keep-alive>
      <router-view v-else></router-view>
    </config-provider>
  `
});

