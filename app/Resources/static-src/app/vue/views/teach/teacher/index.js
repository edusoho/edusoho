import Vue from 'common/vue';
import Router from 'vue-router';
import routes from 'app/vue/router/teach/teacher/index.js';
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const router = new Router({
  mode: 'hash',
  routes
})

new Vue({
  el: '#app',
  router,
  components: {
    AntConfigProvider
  },
  template: `<ant-config-provider />`
});

