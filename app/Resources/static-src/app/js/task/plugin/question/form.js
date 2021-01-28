import notify from 'common/notify';
import postal from 'postal';

export default class {
  constructor() {
    this.$element = $('#task-question-plugin-form');
    this.editor = null;
    this.validator = null;
    this.initEvent();
  }

  initEvent() {
    this.$element.on('focusin', '.expand-form-trigger', event => this.expand());
    this.$element.on('click', '.btn-primary', event => this.save(event));
    this.$element.on('click', '.collapse-form-btn', event => this.collapse());
  }

  save(event) {
    event.preventDefault();

    if (!this.validator || !this.validator.form()) {
      return;
    }
    let $btn = $(event.currentTarget);
    $btn.attr('disabled', 'disabled');

    let channel = postal.channel('task.plugin.question');

    $.post(this.$element.attr('action'), this.$element.serialize())
      .done((html) => {
        notify('success', Translator.trans('task.plugin_question_post_success_hint'));
        channel.publish('form.save', {
          html: html
        });
        $btn.removeAttr('disabled');
        this.collapse();
      });
  }

  expand() {
    if (this.$element.hasClass('form-expanded')) {
      return;
    }

    this.$element.addClass('form-expanded');

    let editor = CKEDITOR.replace('question_content', {
      toolbar: 'Simple',
      fileSingleSizeLimit: app.fileSingleSizeLimit,
      filebrowserImageUploadUrl: this.$element.find('#question_content').data('imageUploadUrl')
    });

    this.editor = editor;

    this.validator = this.$element.validate({
      rules: {
        'question[title]': 'required',
        'question[content]': 'required'
      },
      messages: {
        'question[title]': Translator.trans('task.plugin_question_add.title_required_error_hint'),
        'question[content]': Translator.trans('task.plugin_question_add.content_required_error_hint')
      }
    });

    editor.on( 'change', () => {
      this.$element.find('[name="question[content]"]').val(editor.getData());
    });
    editor.on('blur', () => {
      this.$element.find('[name="question[content]"]').val(editor.getData());
    });

    this.$element.find('.js-detail-form-group').removeClass('hide');
  }

  collapse() {
    this.$element.removeClass('form-expanded');
    this.editor && this.editor.destroy();
    this.$element.removeData('validator');
    this.clear();
    this.$element.find('.js-detail-form-group').addClass('hide');

  }

  clear() {
    this.$element.find('input[type=text],textarea').each(function () {
      $(this).val('');
    });
  }
}