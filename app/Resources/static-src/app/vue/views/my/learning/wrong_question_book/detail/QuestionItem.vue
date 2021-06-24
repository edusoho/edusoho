<template>
  <div class="student-view-question">
    <component
      :is="currentQuestionComponent"
      :question="question.question"
      :report="question.report"
    >
      <template #situation>
        <situation />
      </template>
    </component>
  </div>
</template>

<script>
import SingleChoice from '../question/SingleChoice.vue';
import Choice from '../question/Choice.vue';
import UncertainChoice from '../question/UncertainChoice.vue';
import Judge from '../question/Judge.vue';
import Fill from '../question/Fill.vue';
import Situation from '../question/components/Situation.vue';

export default {
  name: 'StudentView',

  props: {
    question: {
      type: Object,
      required: true
    }
  },

  components: {
    SingleChoice,
    Choice,
    UncertainChoice,
    Judge,
    Fill,
    Situation
  },

  data() {
    return {
      questionComponents: {
        single_choice: 'SingleChoice',
        choice: 'Choice',
        uncertain_choice: 'Choice',
        true_false: 'Judge',
        text: 'Fill'
      }
    }
  },

  computed: {
    currentQuestionComponent() {
      return this.questionComponents[this.question.question.answer_mode];
    }
  }
}
</script>
