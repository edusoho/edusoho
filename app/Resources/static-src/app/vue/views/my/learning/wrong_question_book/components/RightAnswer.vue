<template>
  <div class="clearfix right-answer">
    <div class="pull-left right-answer-label">正确答案：</div>
    <div class="pull-left right-answer-content" v-html="rightAnswer" />
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
    rightAnswer() {
      let { answer, answer_mode } = this.question.questions[0];

      if (answer_mode === 'true_false') {
        answer = _.map(answer, function(item) {
          return item === 'T' ? '正确' : '错误';
        });
      }

      return `<span class="success">${_.join(answer, '、')}<span>`;
    }
  }
}
</script>

<style lang="less" scoped>
.right-answer {
  margin-top: 16px;

  .right-answer-label {
    color: #333;
  }

  .right-answer-content {
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
  .right-answer {
    margin-top: 8px;
  }
}
</style>
