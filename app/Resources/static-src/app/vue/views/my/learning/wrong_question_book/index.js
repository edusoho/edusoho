import Vue from 'common/vue';
import Router from 'vue-router';
import routes from 'app/vue/router/my/learning/wrong_question_book/index.js';
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const router = new Router({
  mode: 'hash',
  routes
});

new Vue({
  el: '#app',
  components: {
    AntConfigProvider
  },
  router,
  template: '<ant-config-provider />'
});

