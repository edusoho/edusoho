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
}

@media (max-width: 767px) {
  .answer-content {
    margin-top: 8px;
  }
}
</style>
