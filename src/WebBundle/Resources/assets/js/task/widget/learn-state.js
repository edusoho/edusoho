class LearnState {
  constructor(option) {
    this.element = $(option.element);
    this.learningPrompt = this.element.find('.js-learning-prompt');
    this.learnedPrompt = this.element.find('.js-learned-prompt');
    this.learnprompt = this.element.find('.js-learn-prompt');
    this.init();
  }

  init() {
    $('.js-test').click(event=>this.learnedPromptMethod(event));
  }

  learnedPromptMethod() {
    this.learnprompt.removeClass('open');
    this.learningPrompt.addClass('moveup');
    window.setTimeout(()=>{ 
      this.learningPrompt.removeClass('moveup');
      this.learnedPrompt.addClass('moveup');
    },3000); 
  }
}

export default LearnState;