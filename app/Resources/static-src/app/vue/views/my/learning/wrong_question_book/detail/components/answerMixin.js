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
    }
  },

  methods: {
    getAnswerClass(value) {
      const { answer } = this.question.question; // 正确答案
      const { response } = this.question.report; // 用户选择的答案

      if (_.includes(_.difference(answer, response), value)) return 'right-answer'; // 用户未选的正确答案

      if (_.includes(_.difference(response, answer), value)) return 'choose-answer--wrong'; // 用户选择的错误答案

      if (_.includes(_.intersection(answer, response), value)) return 'choose-answer--right'; // 用户选中的正确答案
    }
  }
}