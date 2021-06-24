<template>
  <div class="result mb16">
    <div class="clearfix mb8" v-if="question.answer_mode != 'true_false'">
      <div class="pull-left result-label">正确答案：</div>
      <div class="pull-left result-content" v-html="rightAnswer" />
    </div>

    <div class="clearfix">
      <div class="pull-left result-label">答题结果：</div>
      <div class="pull-left result-content" v-html="answerResult" />
    </div>
  </div>
</template>

<script>
import _ from 'lodash';

export default {
  props: {
    question: {
      type: Object,
      required: true
    },

    report: {
      type: Object,
      required: true
    }
  },

  computed: {
    rightAnswer() {
      const { answer } = this.question;
      return `<span class="success">${_.join(answer, '、')}<span>`;
    },

    answerResult() {
      const { response } = this.report;
      return `你的答案是<span class="danger"> ${_.join(response, '、')} </span>, 你答错了。`;
    }
  }
}
</script>

<style lang="less" scoped>
.result {
  .result-label {
    color: #333;
  }

  .result-content {
    width: calc(100% - 72px);

    /deep/ .danger {
      color: #ff5c3b;
    }

    /deep/ .success {
      color: #46c37b;
    }
  }
}
</style>
