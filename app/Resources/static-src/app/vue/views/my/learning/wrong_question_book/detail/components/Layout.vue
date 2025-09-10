<template>
  <div class="question-layout" :order="order">
    <div class="answer-mode-tag">{{ showAnswerModeText(questions.answer_mode) }}</div>

    <stem :order="order" :stem="questions.stem" />

    <div class="prevent-click answer-content">
      <slot name="answer" />
    </div>

    <a-divider v-if="questions.answer_mode !== 'text'" style="margin: 16px 0;" />

    <right-answer v-if="questions.answer_mode !== 'text'" :question="question" />

    <answer-result :question="question" />

    <analysis :analysis="questions.analysis" />

    <situation :question="question" />
  </div>
</template>

<script>
import Stem from 'app/vue/views/components/WrongQuestion/QuestionElement/Stem.vue';
import RightAnswer from 'app/vue/views/components/WrongQuestion/QuestionElement/RightAnswer.vue';
import AnswerResult from 'app/vue/views/components/WrongQuestion/QuestionElement/AnswerResult.vue';
import Analysis from 'app/vue/views/components/WrongQuestion/QuestionElement/Analysis.vue';
import Situation from 'app/vue/views/components/WrongQuestion/QuestionElement/Situation.vue';

export default {
  name: 'question-layout',

  components: {
    Stem,
    RightAnswer,
    AnswerResult,
    Analysis,
    Situation
  },

  props: {
    question: {
      type: Object,
      required: true
    },

    order: {
      type: Number,
      required: true
    }
  },

  computed: {
    questions() {
      return this.question.questions[0];
    }
  },

  onMounted() {
    console.log('question-layout mounted', this.question);
  },

  methods: {
    showAnswerModeText(mode) {
      switch (mode) {
      case 'true_false':
        return '判断题'
      case 'single_choice':
        return '单选题'
      case 'choice':
        return '多选题'
      case 'uncertain_choice':
        return '不定项选择题'
      case 'text':
        return '填空题'
      case 'rich_text':
        return '问答题'
      default:
        return ''
      }
    },
  }
}
</script>

<style lang="less">
.question-layout {
  padding: 16px 0 24px 54px;
  border-bottom: 1px solid #ebebeb;

  .answer-mode-tag {
    position: relative;
    right: 40px;
    margin-bottom: 16px;
    width: fit-content;
    color: #46c37b;
    font-size: 14px;
    font-weight: 400;
    line-height: 20px;
    padding: 2px 8px;
    border-radius: 4px;
    border: 1px solid #46c37b;
  }

  .answer_mode {
    position: relative;
    left: -30px;
    margin-bottom: 16px;
  }

  .answer-content {
    margin-top: 16px;
  }

  .choose-answer {
    display: block;
    position: relative;
    font-weight: 400;

    .ant-radio,
    .ant-checkbox {
      position: absolute;
      top: 2px;
      vertical-align: super;

      & + span {
        display: inline-block;
        margin-left: 16px;
      }
    }

    &.ant-checkbox-wrapper + .ant-checkbox-wrapper {
      margin-left: 0;
    }

    .choose-answer-content {
      display: table;
      white-space: normal;

      .choose-answer-text {
        width: 100%;
        display: table-cell;

        p {
          margin: 0;
        }

        img {
          max-width: 100%!important;
        }

        table {
          width: 100%;
          border-collapse: collapse;
          text-align: center;

          td {
            border: 1px solid #333;
          }

          th {
            border: 1px solid #333;
          }
        }
      }
    }

    &--right {
      color: #46c37b;

      .choose-answer-content {
        color: #46c37b;
      }

      .ant-radio-checked .ant-radio-inner,
      .ant-checkbox-checked .ant-checkbox-inner {
        border-color: #46c37b;

        &::after {
          background-color: #46c37b;
        }
      }

      .ant-checkbox-checked .ant-checkbox-inner {
        background-color: #46c37b;
      }
    }

    &--wrong {
      color: #ff5c3b;

      .choose-answer-content {
        color: #ff5c3b;
      }

      .ant-radio-checked .ant-radio-inner,
      .ant-checkbox-checked .ant-checkbox-inner {
        border-color: #ff5c3b;

        &::after {
          background-color: #ff5c3b;
        }
      }

      .ant-checkbox-checked .ant-checkbox-inner {
        background-color: #ff5c3b;
      }
    }
  }

  .right-answer {
    color: #46c37b !important;
  }
}

@media (max-width: 767px) {
  .answer-content {
    margin-top: 8px;
  }
}
</style>
