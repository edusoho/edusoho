class QuestionForm{
  constructor(){
    this.$element = $('#task-question-plugin-form');
    this.editor = null;
    this.validator = null;
    this.initEvent();
  }

  initEvent() {
    this.$element.on('focusin', '.expand-form-trigger', event => this.expand() )
  }

  expand(){
    if (this.$element.hasClass('form-expanded')) {
      return ;
    }

    this.$element.addClass('form-expanded');

    let editor = CKEDITOR.replace('question_content', {
      toolbar: 'Simple',
      filebrowserImageUploadUrl: this.$element.find('#question_content').data('imageUploadUrl')
    });

    this.editor = editor;

    this.validator = this.$element.validate({
      rules: {
        'question[title]': 'required',
        'question[content]': 'required'
      }
    });

    editor.on('change', () => {
      this.$element.find('question[content]').val(editor.getData());
    });

    /*validator.on('formValidated', function(err, msg, ele) {
      if (err == true) {
        return ;
      }

      $.post($form.attr('action'), $form.serialize(), function(html) {
        pane.$('[data-role=list]').prepend(html);
        pane.$('.empty-item').remove();
        pane.collapseForm();
      }).error(function(response){
        var response = $.parseJSON(response.responseText);
        Notify.danger(response.error.message);
      });
    });*/

    this.$element.find('.detail-form-group').removeClass('hide');
  }
}

class Question{

}

class Questions{

}

class QuestionPlugin{

}

new QuestionForm();