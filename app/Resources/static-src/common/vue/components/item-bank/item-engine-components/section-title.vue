<template>
  <div class="ibs-pl16 ibs-pr16 ibs-engin-section">
    <a-row>
      <a-col :span="24" class="ibs-engin-section__title ibs-pl16">
        {{ name }}
        <span class="ibs-engin-section__span ibs-ml16">
          {{ t("itemEngine.Number_of_topics")(count) }}</span
        >
        <span v-show="needScore" class="ibs-engin-section__span">{{
          t("itemEngine.allScore")(score)
        }}</span>
      </a-col>
    </a-row>
  </div>
</template>

<script>
import Locale from "common/vue/mixins/locale";

export default {
  name: "section-title",
  mixins: [Locale],
  props: {
    name: {
      type: String,
      default: ""
    },
    count: {
      type: String,
      default: ""
    },
    score: {
      type: String,
      default: ""
    },
    needScore: {
      type: Number,
      default: 1
    }
  }
};
</script>
