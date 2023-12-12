<template>
  <div class="">
    <!-- 题目类型 -->
		
    <!-- <sectionTitle
      :questionsType="commonData.questionsType"
      :score="commonData.score"
      :current="commonData.current"
      :showScore="commonData.showScore"
      :all="commonData.all"
    /> -->

    <!-- 题干 -->
    <!-- <stem
      :questionsType="commonData.questionsType"
      :questionSeq="commonData.current"
      :questionStem="commonData.questionStem"
    ></stem> -->

    <!-- <attachement-preview
      v-for="item in getAttachmentTypeData('stem')"
      :attachment="item"
      :key="item.id"
    /> -->

    <slot></slot>

    <!-- 答题结果 -->
    <!-- <report
      v-if="showReport"
      v-bind="$attrs"
      :answer="commonData.answer"
      :report="commonData.report"
      :attachements="getAttachmentTypeData('answer')"
      :answer_mode="commonData.questionsType"
    ></report> -->

    <!-- 答题解析 -->
    <!-- <analysis
      v-if="showAnalysis"
      :mode="mode"
      :analysis="commonData.analysis"
      :answer="commonData.answer"
      :attachements="getAttachmentTypeData('analysis')"
      :answer_mode="commonData.questionsType"
      v-on="$listeners"
    ></analysis> -->

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
// import sectionTitle from "./section-title";
// import report from "./report.vue";
// import analysis from "./analysis.vue";
// import stem from "./stem.vue";
import review from "./review.vue";
// import attachementPreview from "./attachement-preview.vue";

export default {
  components: {
    // sectionTitle,
    // report,
    // analysis,
    // stem,
    review,
    // attachementPreview
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
