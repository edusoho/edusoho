import notify from 'common/notify';
import postal from 'postal';

export default class {
  constructor(url) {
    this.url = url;
    this.$element = $('.question-detail-block');
    this.$form = null;
    this.validator = null;
    this.channel = postal.channel('task.plugin.question');
    this.render();
  }

  initEvent() {

    this.$element.on('click', '.back-to-list', () => {
      this.channel.publish('back-to-list');
    });
    this.toggleShow();
    this.$element.on('click', '.js-more-show', (event) => {
      this.channel.publish('js-more-show', event);
    });


    this.$form.on('click', '.btn-primary', event => this.onSavePost(event));
  }

  toggleShow() {
    this.$element.find('.task-question-plugin-pane-thread__content').each(function () {
      let height = $(this).height();
      if (height >= 76) {
        $(this).next().show();
      }
    });
  }

  onSavePost(event) {
    const self = this;
    event.preventDefault();
    if (!this.validator || !this.validator.form()) {
      return;
    }
    $.post(this.$form.attr('action'), this.$form.serialize())
      .done((html) => {
        if (!$('.js-post-answer-item').length) {
          $('.js-answer-title').removeClass('hidden');
        }
        this.$element.find('[data-role=post-list]').append(html);
        if ($(event.target).data('type') === 'question') {
          self.toggleShow();
        }
        const number = parseInt(this.$element.find('[data-role=post-number]').text());
        this.$element.find('[data-role=post-number]').text(number + 1);
        this.$form.find('textarea').val('');
      })
      .error(function (response) {
        notify('danger', response.error.message);
      });
  }

  render() {
    $.get(this.url)
      .done(html => {
        this.$element.html(html);

        this.$form = this.$element.find('.post-form');
        this.validator = this.$form.validate({
          rules: {
            'post[content]': 'required'
          },
          messages: {
            'post[content]': Translator.trans('task.plugin_question_replay.content_required_error_hint')
          }
        });

        this.initEvent();
      })
      .fail(() => {
        notify('danger', 'error');
      });
  }

  destroy() {
    this.$element.html('');
    this.$element.undelegate();
  }
}