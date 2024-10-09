import subject from './src/attachment.vue';

subject.install = function(Vue) {
  Vue.component(subject.name, subject);
};

export default subject;
