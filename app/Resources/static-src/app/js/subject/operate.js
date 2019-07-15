export default class QuestionOperate {
  constructor() {
    this.questions = $('.js-cached-data').html();
    this.selectQuestion = [];
    this.$statList = $('.js-subject-data');
    this.$itemList = $('.js-item-list');
  }

  modifyDifficulty(selectQuestion, difficulty, text) {
    let self = this;
    $.each(selectQuestion, function(index, position){
      // if (typeof self.questions[position] != 'undefined') {
      //   self.questions[position]['difficulty'] = difficulty;
        self.$itemList.find('#' + position).find('.js-difficulty').html(text);
      // }
    });
  }

  modifyScore(selectQuestion, score) {
    let self = this;
    $.each(selectQuestion, function(index, position) {
      self.$itemList.find(`#${position}`).find('.js-score').html(`${score}分`);
    });
  }

  addQuestion(position, question) {
    this.questions.splice(position, 0, question);
  }

  deleteQuestion(position) {
    this.questions.splice(position, 1);
  }
}