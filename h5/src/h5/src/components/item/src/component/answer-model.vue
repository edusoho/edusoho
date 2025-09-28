<template>
  <div class="">
    <slot></slot>

    <!-- 答题自评 -->
    <review
      v-if="showReview"
      :needScore="commonData.showScore"
      :questionId="commonData.questionId"
      :questionScore="commonData.score"
      :reviewStatus="reviewStatus"
      :key="reviewStatus.status"
      @changeReviewList="changeReviewList"
    ></review>
  </div>
</template>

<script>
import review from "./review.vue";

export default {
  components: {
    review,
  },
  data() {
    return {};
  },
  props: {
    mode: {
      //当前模式
      type: String,
      default: "do"
    },
    commonData: {
      type: Object,
      default() {
        return {};
      }
    },
    showAnalysis: {
      type: Boolean,
      default: false
    },
    showReport: {
      type: Boolean,
      default: false
    },
    showReview: {
      type: Boolean,
      default: false
    },
    attachements: {
      type: Array,
      default: () => []
    },
    reviewStatus: {
      type: Object,
      default() {
        return {};
      }
    }
  },
  computed: {},
  watch: {},
  created() {},
  methods: {
    getAttachmentTypeData(type) {
      return this.attachements.filter(item => item.module === type);
    },
    changeReviewList(status) {
      this.$emit('changeReviewList', status)
    },
  }
};
</script>
