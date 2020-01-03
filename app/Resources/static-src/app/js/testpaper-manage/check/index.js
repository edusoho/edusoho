import {
  testpaperCardFixed,
} from 'app/js/testpaper/widget/part';

$.validator.addMethod('score',function(value,element){
  let isFloat = /^\d+(\.\d)?$/.test(value);
  if (!isFloat){
    return false;
  }

  if (Number(value) <= Number($(element).data('score'))) {
    return true;
  } else {
    return false;
  }

}, $.validator.format(Translator.trans('activity.testpaper_manage.marking_validate_error_hint')));

class CheckTest
{
  constructor($container) {

    this.$container = $container;
    this.checkContent = {};
    this.$form = $container.find('form');
    this.$dialog = $container.find('#testpaper-checked-dialog');
    this.validator = null;
    this._initEvent();
    this._init();
    this._initValidate();
    testpaperCardFixed();
    this.isContinue = false;
    this.passStatus = 'passed';
  }

  _initEvent() {
    this.$container.on('focusin','textarea',event=>this._showEssayInputEditor(event));
    this.$container.on('click','[data-role="check-submit"]',event=>this._submitValidate(event));
    this.$container.on('click','*[data-anchor]',event=>this._quick2Question(event));
    this.$dialog.on('click','[data-role="finish-check"]',event=>this._submit(event));
    this.$dialog.on('click','.js-next-check',event=>this._continue(event));
    this.$dialog.on('change','select',event=>this._teacherSayFill(event));
  }

  _init() {

  }

  _showEssayInputEditor(event) {
    let $shortTextarea = $(event.currentTarget);

    if ($shortTextarea.hasClass('essay-teacher-say-short')) {

      event.preventDefault();
      event.stopPropagation();
      $(this).blur();
      let $longTextarea = $shortTextarea.siblings('.essay-teacher-say-long');
      let $textareaBtn = $longTextarea.siblings('.essay-teacher-say-btn');

      $shortTextarea.hide();
      $longTextarea.show();
      $textareaBtn.show();

      let editor = CKEDITOR.replace($longTextarea.attr('id'), {
        toolbar: 'Minimal',
        fileSingleSizeLimit: app.fileSingleSizeLimit,
        filebrowserImageUploadUrl: $longTextarea.data('imageUploadUrl')
      });

      editor.on('blur', function() {
        editor.updateElement();
        setTimeout(function() {
          $longTextarea.val(editor.getData());
          $longTextarea.change();
        }, 1);
      });

      editor.on('instanceReady', function() {
        this.focus();

        $textareaBtn.one('click', function() {
          $shortTextarea.val($(editor.getData()).text());
          editor.destroy();
          $longTextarea.hide();
          $textareaBtn.hide();
          $shortTextarea.show();
        });
      });

      editor.on('key', function(){
        editor.updateElement();
        setTimeout(function() {
          $longTextarea.val(editor.getData());
          $longTextarea.change();
        }, 1);
      });

      editor.on('insertHtml', function() {
        editor.updateElement();
        setTimeout(function() {
          $longTextarea.val(editor.getData());
          $longTextarea.change();
        }, 1);
      });
    }

  }

  _initValidate() {
    this.validator = this.$form.validate();

    if ($('*[data-score]:visible').length > 0) {
      $('*[data-score]:visible').each(function(){
        $(this).rules('add',{
          required:true,
          score:true,
          min:0,
          messages: {
            required: Translator.trans('activity.testpaper_manage.required_error_hint'),
          }
        });
      });
    }

  }

  _quick2Question(event) {
    let $target = $(event.currentTarget);
    let position = $($target.data('anchor')).offset();
    $(document).scrollTop(position.top - 55);
  }

  _submitValidate() {
    let scoreTotal = 0;

    if (this.validator == undefined || this.validator.form()) {
      let self = this;
      $('*[data-score]').each(function(){
        let content = {};
        let questionId = $(this).data('id');

        content['score'] = Number($(this).val());
        content['teacherSay'] = $('[name="teacherSay_'+questionId+'"]').val();

        self.checkContent[questionId] = content;
        scoreTotal = scoreTotal + Number($(this).val());
      });

      let $scoreItem = this.$dialog.find('.js-student-score');
      let passScore = this.$dialog.find('.js-pass-score').data('passScore');
      let objectiveScore = Number($scoreItem.data('objectiveScore'));
      let totalScore = Number(scoreTotal) + objectiveScore;

      $scoreItem.html(totalScore);
      if (totalScore >= passScore) {
        $scoreItem.removeClass('color-danger').addClass('color-success');
        this.passStatus = 'passed';
      } else {
        $scoreItem.removeClass('color-success').addClass('color-danger');
        this.passStatus = 'unpassed';
      }
      this.$dialog.modal('show');
    }


  }

  _continue(event) {
    this.isContinue = true;
    this._submit(event);
  }

  _submit(event) {

    let $target = $(event.currentTarget);
    let teacherSay = this.$dialog.find('textarea').val();
    let passedStatus = '';
    if (this.$dialog.find('[name="passedStatus"]:checked').length > 0) {
      passedStatus = this.$dialog.find('[name="passedStatus"]:checked').val();
    } else {
      passedStatus = this.passStatus;
    }

    $target.button('loading');
    $.post($target.data('postUrl'), {result:JSON.stringify(this.checkContent),teacherSay:teacherSay,passedStatus:passedStatus,isContinue:this.isContinue}, function(response) {
      if (response.goto != '') {
        window.location.href = response.goto;
      } else {
        window.location.reload();
      }
    });
  }

  _teacherSayFill(event) {
    let $target = $(event.currentTarget);
    let $option = $target.find('option:selected');

    if ($option.val() == '') {
      this.$dialog.find('textarea').val('');
    } else {
      this.$dialog.find('textarea').val($option.text());
    }
  }
}

new CheckTest($('.js-testpaper-container'));