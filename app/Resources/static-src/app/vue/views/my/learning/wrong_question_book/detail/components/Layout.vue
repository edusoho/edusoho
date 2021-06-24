<template>
  <div class="question-layout">
    <stem :order="1" :stem="question.question.stem" />

    <div class="prevent-click answer-content">
      <slot name="answer" />
    </div>

    <a-divider style="margin: 16px 0;" />

    <right-answer :question="question" />

    <answer-result :question="question" />

    <analysis :analysis="question.question.analysis" />

    <situation :question="question" />
  </div>
</template>

<script>
import Stem from '../../components/Stem.vue';
import RightAnswer from '../../components/RightAnswer.vue';
import AnswerResult from '../../components/AnswerResult.vue';
import Analysis from '../../components/Analysis.vue';
import Situation from '../../components/Situation.vue';

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

      .choose-answer-text {
        display: table-cell;

        p {
          margin: 0;
        }
      }
    }

    &--right {
      color: #46c37b;
    }

    &--right {
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
