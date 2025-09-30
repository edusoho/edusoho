<template>
  <div class="clearfix answer-result">
    <div class="pull-left answer-result-label">{{ 'components.wrong_question.question_element.answer_result.answering_result' | trans }}：</div>
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
      let { answer, answer_mode, report: { response } } = this.question.questions[0];

      if (answer_mode === 'true_false') {
        response = _.map(response, function(item) {
          return item === 'T' ? Translator.trans('components.wrong_question.question_element.answer_result.correct') : Translator.trans('components.wrong_question.question_element.answer_result.mistake');
        });
      }

      if (answer_mode === 'text') {
        let result = '';
        _.forEach(answer, function(item, index) {
          result += `<div>${ Translator.trans('components.wrong_question.question_element.answer_result.gap_filling') }(${index + 1})：${ Translator.trans('components.wrong_question.question_element.right_answer.right_answers') }：<span class="success">${item}</span>， ${ Translator.trans('components.wrong_question.question_element.right_answer.your_answer') }：<span class="danger">${response.length > 0 ? response[index] : Translator.trans('components.wrong_question.question_element.right_answer.not_answered')}</span></div>`;
        });
        return result;
      }

      return `${ Translator.trans('components.wrong_question.question_element.right_answer.your_answer_is') }<span class="danger"> ${_.join(response, '、')} </span>, ${ Translator.trans('components.wrong_question.question_element.right_answer.answered_incorrectly') }。`;
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
