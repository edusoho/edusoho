import Vue from 'common/vue';
import Router from 'vue-router'
import routes from 'app/vue/router/teach/multi_class_product/index.js'
import zhCN from 'ant-design-vue/lib/locale-provider/zh_CN';

const router = new Router({
  mode: 'hash',
  routes
})

new Vue({
  el: '#app',
  router,
  data: {
    locale: zhCN
  },
  template: `
    <a-config-provider :locale="locale">
      <router-view></router-view>
    </a-config-provider>
  `
})

