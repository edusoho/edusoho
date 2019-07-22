import QuestionChoice from './choice-question';
import ReactDOM from 'react-dom';
import React from 'react';
import QuestionOptions from 'app/common/component/question-options';
import BaseQuestion from './base-question';

class SingleChoice extends BaseQuestion {
  constructor($form) {
    super($form);
    this.validator = null;
    this.oldFieldId = 'question-stem-field';
    this.fieldId = '';
    this.optionCount = 0;
    this.init();
  }

  initOptions() {
    ReactDOM.render( <QuestionOptions imageUploadUrl={this.imageUploadUrl} imageDownloadUrl={this.imageDownloadUrl} dataSource={this.dataSource} dataAnswer={this.dataAnswer}  isRadio={true}/>,
      document.getElementById('question-options')
    );
  }

  init() {
    this.initOptionCount();
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
      }
    }

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
          noMoreThan: '#score',
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
        }
      },
      messages: {
        missScore: {
          noMoreThan: '漏选分值不得超过题目分值'
        },
        stem: {
          required: '题干内容不得为空'
        },
        options: {
          required: '选项内容不得为空'
        }
      },
      errorPlacement: function(error, element) {
        let elementName = element.attr('name');
        if (elementName == 'right') {
          $('.edit-subject-item__order').addClass('edit-subject-item__order--error');
          cd.message({
            type: 'danger',
            message: Translator.trans('请选择正确答案'),
          });
        } else if (elementName == 'stem' && element.hasClass('hidden')) {
          $('#cke_question-stem-field').after(error);
        } else if (elementName == 'options' && element.hasClass('hidden')) {
          error.appendTo(element.parent());
        } else {
          error.insertAfter(element);
        }
      }
    });
  }

  editOption(event) {
    const $input = $(event.currentTarget);
    const $textArea = $input.next();
    this.fieldId = $textArea.attr('id');

    $input.hide();
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
      $oldTarget.prev('input').show().val(data);
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
      console.log(editor.getData());
    });
    editor.on('blur', () => {
      $target.val(editor.getData());
      console.log(editor.getData());
      // $target.addClass('hidden');
      // $(`#cke_${self.fieldId}`).addClass('hidden');
      // $('.js-upload-stem-attachment').addClass('hidden');
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
    $editItem.nextAll('.edit-subject-item').each(function() {
      let $order = $(this).find('.edit-subject-item__order');
      let oldOrderText = $order.text();
      $order.text(orderText);
      orderText = oldOrderText;
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
}

export default SingleChoice;