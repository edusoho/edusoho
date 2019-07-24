import BaseQuestion from './base-question';

class Choice extends BaseQuestion {
  constructor($form, object) {
    super($form, object);
    this.validator = null;
    this.oldFieldId = 'question-stem-field';
    this.fieldId = '';
    this.optionCount = 0;
    this.init();
  }

  init() {
    this.initOptionCount();
    this.initData();
    this.initEvent();
    this.initValidator();
    this.initTitleEditor(this.validator);
  }

  initOptionCount() {
    this.optionCount = $('.edit-subject-item').length;
  }

  initEvent() {
    this.$form.on('focus', '.js-item-option-edit', event => this.editOption(event));
    this.$form.on('click', '.js-item-option-delete', event => this.deleteOption(event));
    this.$form.on('click', '.js-item-option-add', event => this.addOption(event));
  }

  initData() {
    $('.cd-checkbox.checked').find('[name="right"]').attr('checked', true);
    $('[name=options]').each(function() {
      $(this).prev().val($(this).val().replace(/<\s?img[^>]*>/gi, '【图片】').replace(/<p>|<\/p>/gi, ''));
    })
  }

  initValidator() {
    if ($.validator) {
      $.validator.prototype.elements = function() {
        let validator = this,
            rulesCache = {};
        return $(this.currentForm)
            .find('input')
            .not(':submit, :reset, :image, [disabled]')
            .not(this.settings.ignore)
            .filter(function () {
              if (!this.name && validator.settings.debug && window.console) {
                console.error("%o has no name assigned", this);
              }
              rulesCache[this.name] = true;
              return true;
            });
      };
      $.validator.addMethod('noMoreThan', function(value, element, param) {
        return value <= $(param).val();
      }, 'Please enter a lesser value.' );
      $.validator.addMethod('multi', function(value, element, param) {
        if ($('[name="type"]').val() != 'choice') {
          return true;
        }
        return $('input:checkbox[name="right"]:checked').length > 1;
      });
    }

    this.$form.validate().destroy();
    this.validator = this.$form.validate({
      rules: {
        score: {
          required: true,
          digits: true,
          max: 999,
          min: 0,
          es_score: true
        },
        missScore: {
          required: false,
          digits: true,
          max: 999,
          min: 0,
          noMoreThan: '[name="score"]',
          es_score: true
        },
        stem: {
          required: true,
        },
        'options[]': {
          required: true,
        },
        right: {
          required: true,
          multi: true,
        }
      },
      messages: {
        missScore: {
          noMoreThan: '漏选分值不得超过题目分值'
        },
        stem: {
          required: '题干内容不得为空'
        },
        'options[]': {
          required: '选项内容不得为空'
        }
      },
      errorPlacement: function(error, element) {
        let elementName = element.attr('name');
        if (elementName == 'right') {
          $('.edit-subject-item__order').addClass('edit-subject-item__order--error');
          let message = $('[name="type"]').val() == 'choice' ? Translator.trans('至少选择2个答案') : Translator.trans('请选择正确答案');
          cd.message({
            type: 'danger',
            message: message,
          });
        } else if (elementName == 'stem' && element.hasClass('hidden')) {
          $('#cke_question-stem-field').after(error);
        } else if (elementName == 'options[]' && element.hasClass('hidden')) {
          error.appendTo(element.parent());
        } else if (elementName == 'options[]') {
          error.insertAfter(element.next('[name="options"]'));
        } else {
          error.insertAfter(element);
        }
      }
    });
  }

  editOption(event) {
    const $input = $(event.currentTarget);
    const $textArea = $input.nextAll('[name="options"]').first();
    this.fieldId = $textArea.attr('id');
    $input.addClass('hidden');
    // if (fieldId === 'question-stem-field') {
    //   $('.js-upload-stem-attachment').removeClass('hidden');
    // } else {
    //   $('.js-upload-stem-attachment').addClass('hidden');
    // }

    let $target = $('#' + this.fieldId);
    let editor = null;

    if (CKEDITOR.instances[this.oldFieldId]) {
      editor = CKEDITOR.instances[this.oldFieldId];
      let $oldTarget = $('#' + this.oldFieldId);
      $oldTarget.addClass('hidden');
      let data = editor.getData().replace(/<\s?img[^>]*>/gi, '【图片】').replace(/<p>|<\/p>/gi, '');
      $oldTarget.prevAll('input').first().removeClass('hidden').show().val(data);
      CKEDITOR.instances[this.oldFieldId].destroy();
    }
    this.oldFieldId = this.fieldId;

    if (CKEDITOR.instances[this.fieldId]) {
      CKEDITOR.instances[this.fieldId].destroy();
    }
    editor = CKEDITOR.replace(this.fieldId, {
      toolbar: 'Minimal',
      fileSingleSizeLimit: app.fileSingleSizeLimit,
      filebrowserImageUploadUrl: $target.data('imageUploadUrl'),
      height: $target.height(),
      startupFocus: true,
    });
    editor.focus();

    editor.on('change', () => {
      $target.val(editor.getData());
      let data = editor.getData().replace(/<\s?img[^>]*>/gi, '【图片】').replace(/<p>|<\/p>/gi, '');
      $target.prev('input').val(data);
    });
    editor.on('blur', () => {
      $target.val(editor.getData());
      let data = editor.getData().replace(/<\s?img[^>]*>/gi, '【图片】').replace(/<p>|<\/p>/gi, '');
      $target.prev('input').val(data);
    });
  }

  deleteOption(event) {
    if (this.optionCount <= 2) {
      cd.message({
        type: 'danger',
        message: Translator.trans('选项最少2个'),
      });
      return;
    }

    const $editItem = $(event.currentTarget).parents('.edit-subject-item');
    let orderText = $editItem.find('.edit-subject-item__order').text();
    let radioValue = $editItem.find('input[name="right"]').val();
    $editItem.nextAll('.edit-subject-item').each(function() {
      let $order = $(this).find('.edit-subject-item__order');
      let oldOrderText = $order.text();
      $order.text(orderText);
      orderText = oldOrderText;
      $(this).find('input[name="right"]').val(radioValue);
      radioValue++;
    });

    $editItem.remove();
    this.optionCount--;
  }

  addOption(event) {
    if (this.optionCount >= 10) {
      cd.message({
        type: 'danger',
        message: Translator.trans('选项最多10个'),
      });
      return;
    }

    const $prev = $(event.currentTarget).parent().prev();
    const type = $('[name="type"]').val();

    $.ajax({
      url: `/subject/option/${type}`,
      type: 'get',
      data: {order: this.optionCount+1}
    }).done(resp => {
      $prev.after(resp);
      this.optionCount++;
    });
  }

  submitForm(event) {
    const $editItem = $(event.currentTarget).parents('.subject-edit-item');
    const $item = $('.subject-item.hidden');
    const token = $item.attr('id');

    this.validator.resetForm();
    $('.edit-subject-item__order--error').removeClass('edit-subject-item__order--error');
    // $('.form-error-message').remove();
    if (this.validator.form()) {
      $(event.currentTarget).button('loading');
      this.saveToCache(token, this.$form.serializeArray());
      $editItem.remove();
      $item.removeClass('hidden');
    }
  }

  saveToCache(token, data) {
    let question = {
      options: [],
      answers: [],
    };
    $.each(data, function(index, item) {
      let name = item['name'],
          value = item['value'];
      if ($.inArray(name, ['stem', 'difficulty', 'score', 'missScore', 'type']) !== -1) {
        question[name] = value;
      } else if (name == 'options') {
        question['options'].push(value);
      } else if (name == 'right') {
        question['answers'].push(value);
      }
    });
    this.questionOperate.updateQuestion(token, question);
    console.log(question);
  }
}

export default Choice;