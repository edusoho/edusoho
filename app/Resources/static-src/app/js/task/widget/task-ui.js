export default class TaskUi {
  constructor(option) {
    this.element = $(option.element);
    this.learningPrompt = this.element.find('.js-learning-prompt');
    this.learnedPrompt = this.element.find('.js-learned-prompt');
    this.learnprompt = this.element.find('.js-learn-prompt');
    this.btnLearn = this.element.find('.js-btn-learn');
  }

  learnedWeakPrompt() {
    this.learnprompt.removeClass('open');
    this.learningPrompt.addClass('moveup');
    window.setTimeout(() => {
      this.learningPrompt.removeClass('moveup');
      this.learnedPrompt.addClass('moveup');
      this.learnedPrompt.popover('show');

      window.setTimeout(() => {
        this.learnedPrompt.popover('hide');
      }, 2000);

    }, 2000);
  }


  learned() {
    this.btnLearn.addClass('active');
  }
}
