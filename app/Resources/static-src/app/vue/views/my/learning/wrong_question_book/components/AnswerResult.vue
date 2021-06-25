<template>
  <div class="clearfix answer-result">
    <div class="pull-left answer-result-label">答题结果：</div>
    <div class="pull-left answer-result-content" v-html="answerResult" />
  </div>
</template>

<script>
import _ from 'lodash';

export default {
  props: {
    question: {
      type: Object,
      required: true
    }
  },

  computed: {
    answerResult() {
      let { response } = this.question.report;
      const { answer } = this.question.question;
      const { answer_mode } = this.question.question;

      if (answer_mode === 'true_false') {
        response = _.map(response, function(item) {
          return item === 'T' ? '正确' : '错误';
        });
      }

      if (answer_mode === 'text') {
        let result = '';
        _.forEach(answer, function(item, index) {
          result += `<div>填空(${index + 1})：正确答案：<span class="success">${item}</span>， 你的答案：<span class="danger">${response[index]}</span></div>`;
        });
        return result;
      }

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
    color: #666;

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
