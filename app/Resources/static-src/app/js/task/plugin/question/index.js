import postal from 'postal';
import QuestionForm from './form';
import Question from './question';

class QuestionPlugin {
  constructor() {
    this.$element = $('.question-pane');
    this.$list = this.$element.find('.question-list-block');
    this.$detail = this.$element.find('.question-detail-block');
    this.form = new QuestionForm();
    this.question = null;
    this.initEvent();
  }

  initEvent() {

    this.$element.on('click', '.js-redirect-question-detail', event => this.onRedirectQuestion(event));

    const channel = postal.channel('task.plugin.question');

    channel.subscribe('form.save', (data, envelope) => {
      this.$element.find('[data-role="list"]').prepend(data.html);
      this.$element.find('.empty-item').remove();
    });

    channel.subscribe('back-to-list', () => this.onBackList());
    channel.subscribe('js-more-show', (event) => this.onToggleShow(event));

    $('[data-toggle=\'popover\']').popover();
  }

  onRedirectQuestion(event) {
    const $target = $(event.currentTarget);
    const url = $target.data('url');
    this.question = new Question(url);
    this.$list.hide();
    this.$detail.show();
  }

  onBackList(){
    this.question && this.question.destroy();
    this.$list.show();
    this.$detail.hide();
  }

  onToggleShow(event) {
    const $target = $(event.currentTarget);
    $target.find('.js-change-btn').toggle();
    $target.prev().toggleClass('active');
  }
}

new QuestionPlugin();
