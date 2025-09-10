import itemEngine from "./src/item-engine.vue";

itemEngine.install = function(Vue) {
  Vue.component(itemEngine.name, itemEngine);
};

export default itemEngine;
