<template>
  <div class="clearfix answer-result">
    <div class="pull-left answer-result-label">答题结果：</div>
    <div class="pull-left answer-result-content" v-html="answerResult" />
  </div>
</template>

<script>
export default {
  props: {
    question: {
      type: Object,
      required: true
    }
  },

  computed: {
    answerResult() {
      const { response } = this.question.report;
      return `你的答案是<span class="danger"> ${_.join(response, '、')} </span>, 你答错了。`;
    }
  }
}
</script>

<style lang="less" scoped>
.answer-result {
  margin-top: 16px;

  .answer-result-label {
    color: #333;
  }

  .answer-result-content {
    width: calc(100% - 72px);

    /deep/ .danger {
      color: #ff5c3b;
    }

    /deep/ .success {
      color: #46c37b;
    }
  }
}

@media (max-width: 767px) {
  .answer-result {
    margin-top: 8px;
  }
}
</style>
