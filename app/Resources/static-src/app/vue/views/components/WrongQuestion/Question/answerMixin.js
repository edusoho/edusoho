import _ from 'lodash';
import Layout from './Layout.vue';

export default {

  components: {
    Layout
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

  methods: {
    getAnswerClass(value) {
      const { answer } = this.questions;
      if (_.includes(answer, value)) return 'choose-answer--right';
    }
  }
}