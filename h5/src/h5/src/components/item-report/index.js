import itemReport from "./src/item-report.vue";

itemReport.install = function(Vue) {
  Vue.component(itemReport.name, itemReport);
};

export default itemReport;
