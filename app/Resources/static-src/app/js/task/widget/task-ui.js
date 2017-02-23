export default class TaskUi {
  constructor(option) {
    this.element = $(option.element);
    this.learningPrompt = this.element.find('.js-learning-prompt');
    this.learnedPrompt = this.element.find('.js-learned-prompt');
    this.learnprompt = this.element.find('.js-learn-prompt');
    this.btnLearn = this.element.find('.js-btn-learn');
  }

  learnedWeakPrompt() {
    console.log(this.learnprompt);
    console.log(this.learningPrompt);
    this.learnprompt.removeClass('open');
    this.learningPrompt.addClass('moveup');
    window.setTimeout(() => {
      this.learningPrompt.removeClass('moveup');
      this.learnedPrompt.addClass('moveup');
    }, 3000);
  }


  learned() {
    this.btnLearn.addClass('active');
  }
}
