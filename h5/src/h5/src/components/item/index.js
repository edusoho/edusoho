import item from "./src/item.vue";

item.install = function(Vue) {
  Vue.component(item.name, item);
};

export default item;
