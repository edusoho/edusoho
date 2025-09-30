export default {
  methods: {
    reportAnswer(mode, i, userAnwer, rightAnswer) {
      let classNames = "";
      if (mode !== "report") {
        return "";
      }
      if (userAnwer.includes(i)) {
        classNames += "ibs-subject-option__order_wrong ";
      }
      if (rightAnswer.includes(i)) {
        classNames += "ibs-subject-option__order_right";
      }
      return classNames;
    }
  }
};
