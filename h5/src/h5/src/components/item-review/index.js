import itemReview from "./src/item-review.vue";

itemReview.install = function(Vue) {
  Vue.component(itemReview.name, itemReview);
};

export default itemReview;
