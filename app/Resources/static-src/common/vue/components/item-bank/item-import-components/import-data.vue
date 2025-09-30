<template>
  <div class="ibs-import-list">
    <div class="ibs-clearfix ibs-subject-rect__title">
      <span class="ibs-left">{{ t("itemImport.Topic_statistics") }}</span>
      <span
        class="ibs-right ibs-subject-data__num"
        v-show="importType === 'item'"
        >{{ t("itemImport.Number_of_topics")(totalItem) }}
      </span>
    </div>
    <div
      class="ibs-clearfix ibs-subject-data__item"
      v-show="importType === 'testpaper'"
    >
      <span class="ibs-left ibs-subject-data__num">{{
        t("itemImport.Number_of_topics")(totalItem)
      }}</span>
      <span class="ibs-right ibs-subject-data__num">{{
        t("itemImport.allScore")(allScore)
      }}</span>
    </div>
    <div
      class="ibs-clearfix ibs-subject-data__item"
      v-for="(value, key, index) of totalTypes"
      :key="index"
    >
      <span class="ibs-left">{{ getTypeName(key) }}</span>
      <span class="ibs-right ibs-subject-data__num">{{
        t("itemImport.Number_of_topics")(value)
      }}</span>
    </div>
  </div>
</template>
<script>
import Locale from "common/vue/mixins/locale";
export default {
  name: "import-data",
  mixins: [Locale],
  props: {
    totalItem: {
      type: Number,
      default: 0
    },
    totalTypes: {
      type: Object,
      default() {
        return {};
      }
    },
    importType: {
      type: String,
      default: "item"
    },
    allScore: {
      type: Number,
      default: 0
    }
  },
  methods: {
    getTypeName(type) {
      switch (type) {
        case "single_choice":
          return this.t("single_choice");
        case "fill":
          return this.t("fill");
        case "essay":
          return this.t("essay");
        case "material":
          return this.t("material");
        case "uncertain_choice":
          return this.t("uncertain_choice");
        case "choice":
          return this.t("choice");
        case "determine":
          return this.t("determine");
        default:
          return "";
      }
    }
  }
};
</script>
