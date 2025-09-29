<template>
  <div class="clearfix analysis">
    <div class="pull-left analysis-label">{{ 'components.wrong_question.question_element.answer_result.answering_result' | trans }}：</div>
    <div class="pull-left analysis-content" v-html="analysis || noAnalysisText()"></div>
  </div>
</template>

<script>
export default {
  props: {
    analysis: {
      type: String,
      required: true
    }
  },
  methods: {
    noAnalysisText() {
      return Translator.trans('components.wrong_question.question_element.analysis.no_explanation');
    }
  }
}
</script>

<style lang="less" scoped>
.analysis {
  padding: 16px 26px 16px 0;
  margin-top: 16px;
  background: rgba(70, 195, 123, 0.05);
  border-radius: 4px;

  .analysis-label {
    text-indent: 2em;
    color: #333;
  }

  .analysis-content {
    width: calc(100% - 72px);
    color: #666;
  }

  /deep/ p {
    margin: 0;
  }

  /deep/ .analysis-content img {
    max-width: 100%!important;
  }
}

@media (max-width: 767px) {
  .analysis {
    padding: 8px 8px 8px 0;
    margin-top: 8px;
  }
}
</style>
