<template>
  <component
    :is="currentQuestionComponent(question.type)"
    :question="question"
    :order="1"
  />
</template>

<script>
import _ from 'lodash';
import SingleChoice from './SingleChoice.vue';
import Choice from './Choice.vue';
import Judge from './Judge.vue';
import Fill from './Fill.vue';

export default {
  props: {
    question: {
      type: Object,
      required: true
    }
  },

  components: {
    SingleChoice,
    Choice,
    Judge,
    Fill
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

  methods: {
    currentQuestionComponent(answerMode) {
      return this.questionComponents[answerMode];
    }
  }
}
</script>
