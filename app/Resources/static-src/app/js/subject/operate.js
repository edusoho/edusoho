export default class QuestionOperate {
  constructor($question) {
    this.question = $question;
    this.selectQuestion = [];
    this.$statList = $('.js-subject-data');
    this.$itemList = $('.js-item-list');
  }

  modifyDifficulty(selectQuestion, difficulty, text) {
    let newQuestions = this.question;
    let self = this;
    $.each(selectQuestion, function(index, questionId){
      // if (typeof newQuestions[questionId] != 'undefined') {
      //   newQuestions[questionId]['difficulty'] = difficulty;
      self.$itemList.find('#' + questionId).find('.js-difficulty').html(text);
      // }
    });
  }
}