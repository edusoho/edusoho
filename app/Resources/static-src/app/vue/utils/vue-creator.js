import Vue from 'common/vue';
import Router from 'vue-router';
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const createVueApp = (el, routes) => {
  const router = new Router({
    routes
  });

  new Vue({
    el,
    router,
    components: {
      AntConfigProvider
    },
    template: '<ant-config-provider/>'
  });
};

export { createVueApp };
