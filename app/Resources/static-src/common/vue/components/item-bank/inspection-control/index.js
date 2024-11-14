import subject from './src/index.vue';

subject.install = function(Vue) {
  Vue.component(subject.name, subject);
};

export default subject;
