import BaseQuestion from './base-question';

class Choice extends BaseQuestion {
  constructor($form, object) {
    super($form, object);
    this.validator = null;
    this.oldFieldId = 'question-stem-field';
    this.fieldId = '';
    this.optionCount = 0;
    this.timer = false;
    this.errorMessage = {
      noAnswer: Translator.trans('subject.choice_answer_least_two'),
    };
    this.init();
  }

  init() {
    this.initTitleEditor(this.validator);
    this.initOptionCount();
    this.initData();
    this.initEvent();
    this.initValidator();
  }

  initOptionCount() {
    this.optionCount = $('.edit-subject-item').length;
  }

  initEvent() {
    this.$form.on('focus', '.js-item-option-edit', event => this.editOption(event));
    this.$form.on('click', '.js-item-option-delete', event => this.deleteOption(event));
    this.$form.on('click', '.js-item-option-add', event => this.addOption(event));
    this.$form.on('click', '[data-role="edit"]', event => this.editOption(event));
  }

  initData() {
    $('.cd-checkbox.checked').find('[name="right"]').attr('checked', true);
  }

  initValidator() {
    if ($.validator) {
      $.validator.prototype.elements = function() {
        let validator = this,
            rulesCache = {};
        return $(this.currentForm)
            .find('input, textarea')
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
        if (value == '') {
          return true;
        } else {
          return parseFloat(value) <= parseFloat($(param).val());
        }
      }, 'Please enter a lesser value.' );
      $.validator.addMethod('multi', function(value, element, param) {
        return $('input:checkbox[name="right"]:checked').length > 1;
      });
    }

    let self = this;
    this.$form.validate().destroy();
    this.validator = this.$form.validate({
      onkeyup: false,
      rules: {
        score: {
          required: true,
          max: 999,
          min: 0,
          es_score: true
        },
        missScore: {
          required: false,
          max: 999,
          min: 0,
          noMoreThan: '[name="score"]',
          es_score: true
        },
        stem: {
          required: true,
        },
        options: {
          required: true,
        },
        right: {
          required: true,
          multi: true,
        }
      },
      messages: {
        missScore: {
          noMoreThan: Translator.trans('subject.miss_score_no_more_than_score')
        },
        stem: {
          required: Translator.trans('subject.validate.stem_required')
        },
        options: {
          required: Translator.trans('subject.validate.option_required')
        }
      },
      errorPlacement: function(error, element) {
        let elementName = element.attr('name');
        if (elementName == 'right') {
          $('.edit-subject-item__order').addClass('edit-subject-item__order--error');
          cd.message({
            type: 'danger',
            message: self.errorMessage['noAnswer'],
          });
        } else if (elementName == 'stem') {
          $('#cke_question-stem-field').after(error);
        } else if (elementName == 'options' && element.prev().hasClass('hidden')) {
          error.appendTo(element.parent());
        } else {
          error.insertAfter(element);
        }
      }
    });
  }

  editOption(event) {
    let self = this;
    const $input = $(event.currentTarget);
    const $textArea = $input.next();
    this.fieldId = $textArea.attr('id');
    $input.addClass('hidden');

    let $target = $('#' + this.fieldId);
    let editor = null;

    if (CKEDITOR.instances[this.oldFieldId]) {
      editor = CKEDITOR.instances[this.oldFieldId];
      let $oldTarget = $('#' + this.oldFieldId);
      $oldTarget.addClass('hidden');
      $oldTarget.prevAll('input').first().removeClass('hidden').show().val($(self.replacePicture(editor.getData())).text());
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
      $target.prev('input').val($(self.replacePicture(editor.getData())).text());
    });
    editor.on('blur', () => {
      $target.val(editor.getData());
      $target.prev('input').val($(self.replacePicture(editor.getData())).text());
    });
  }

  deleteOption(event) {
    if (this.optionCount <= 2) {
      cd.message({
        type: 'danger',
        message: Translator.trans('subject.choice_option_least_two'),
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
    const self = this;
    if (self.timer) clearTimeout(self.timer);
    self.timer = setTimeout(() => {
      self.addRealOperate(event);
    }, 500);
  }

  addRealOperate(event) {
    let $target = $(event.currentTarget);
    if (this.optionCount >= 10) {
      cd.message({
        type: 'danger',
        message: Translator.trans('subject.choice_option_most_ten'),
      });
      $target.attr('disabled', false);
      return;
    }

    const type = $('[name="type"]').val();

    $.ajax({
      url: `/subject/option/${type}`,
      type: 'get',
      data: {order: this.optionCount}
    }).done(resp => {
      $(event.currentTarget).parent().before(resp);
      this.optionCount++;
    });
  }

  submitForm(event) {
    if (this.optionCount < 2) {
      cd.message({
        type: 'danger',
        message: Translator.trans('subject.choice_option_least_two'),
      });
      return;
    }
    this.validator.resetForm();
    if (this.validator.form()) {
      $(event.currentTarget).button('loading');
      this.finishEdit(this.$form.serializeArray());
    }
  }

  finishEdit(data) {
    let token = $('.js-hidden-token').val();
    let self = this;
    let question = {
      options: [],
      answers: [],
    };
    $.each(data, function(index, item) {
      let name = item['name'],
          value = item['value'];
      if ($.inArray(name, ['stem', 'difficulty', 'score', 'missScore', 'type', 'analysis', 'categoryId']) !== -1) {
        question[name] = value;
      } else if (name == 'options') {
        question['options'].push(value);
      } else if (name == 'right') {
        question['answers'].push(value);
      }
    });
    question['attachment'] = this.getAttachments();
    let method = $('.js-hidden-method').val();
    let isSub = $('.js-sub-judge').val();
    let key = $('.js-edit-form-seq').text() - 1;
    let seq = 0;
    if (isSub == '1') {
      token = self.updateCachedSubQuestion(token, key, question, method);
      question = self.operate.getSubQuestion(token, key);
      seq = key + 1;
    } else {
      token = self.updateCachedQuestion(token, question, method);
      question = self.operate.getQuestion(token);
      seq = self.operate.getQuestionOrder(token);
    }
    this.analysisEditor.destroy();
    $.post(self.$form.data('url'), {'seq': seq, 'question': question, 'token': token, 'isSub': isSub}, html => {
      self.$form.parent('.subject-item').replaceWith(html);
      self._initTooltip();
    });
  }
}

export default Choice;