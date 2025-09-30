<template>
  <div class="ibs-import-footer" id="ibs-import-footer">
    <div class="ibs-import-tap ibs-import-classification ibs-import__category">
      {{ t("itemImport.Category")
      }}<span>
        <a-tooltip placement="top" :getPopupContainer="getPopupContainer">
          <template slot="title">
            <span>{{ getCategoryName(categoryName) }}</span>
          </template>
          <span>{{ getCategoryName(categoryName) }}</span>
        </a-tooltip>
      </span>
    </div>
    <div class="ibs-import-tap ibs-import-classification">
      {{ t("itemImport.Difficulty")
      }}<span>{{ getDifficulty(difficulty) }}</span>
    </div>
    <div class="ibs-import-tap ibs-import-classification">
      {{ t("itemImport.Score") }}<span>{{ score }}</span>
    </div>
  </div>
</template>
<script>
import Locale from "common/vue/mixins/locale";
export default {
  name: "item-footer",
  mixins: [Locale],
  props: {
    difficulty: {
      type: String,
      default: ""
    },
    score: {
      type: String,
      default: ""
    },
    categoryName: {
      type: String,
      default: ""
    }
  },
  methods: {
    getPopupContainer() {
      return document.getElementById("ibs-import-footer");
    },
    getDifficulty(value) {
      switch (value) {
        case "simple":
          return this.t("Simple");
        case "normal":
          return this.t("Normal");
        case "difficulty":
          return this.t("Difficult");
        default:
          return "";
      }
    },
    getCategoryName(value) {
      if (!value) {
        return this.t("None");
      }
      return value;
    }
  }
};
</script>
