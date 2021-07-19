<template>
  <div class="question-layout" :order="order">
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
  }
}
</script>

<style lang="less">
.question-layout {
  padding: 16px 0 24px 54px;
  border-bottom: 1px solid #ebebeb;

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
      color: #666;

      .choose-answer-text {
        display: table-cell;

        p {
          margin: 0;
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
    color: #46c37b;
  }
}

@media (max-width: 767px) {
  .answer-content {
    margin-top: 8px;
  }
}
</style>
