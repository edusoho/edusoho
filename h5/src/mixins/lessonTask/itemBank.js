export default {
  methods: {
    checkAnswer(index, itemdata) {
      const rightanswer = itemdata.answer;
      if (itemdata.testResult && itemdata.testResult.answer) {
        const answer = itemdata.testResult.answer || [];
        if (rightanswer.includes(index) && answer.includes(index)) {
          return 'subject-option__order_right';
        } else if (
          (rightanswer.includes(index) && !answer.includes(index)) ||
                (!rightanswer.includes(index) && answer.includes(index))
        ) {
          return 'subject-option__order_wrong';
        }
      }
      return '';
    }
  }
};
