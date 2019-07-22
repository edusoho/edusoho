import QuestionOperate from './operate';
import showCkEditor from './edit';
import { numberConvertLetter } from '../../common/unit';

export default class sbList {
  constructor() {
    this.$element = $('.js-subject-list');
    this.$itemList = $('.js-item-list');
    this.$batchBtn = $('.js-batch-btn');
    this.$batchWrap = $('.js-subject-wrap');
    this.$sbCheckbox = $('.js-show-checkbox');
    this.$finishBtn = $('.js-finish-btn');
    this.$allBtn = $('.js-batch-select');
    this.$anchor = $('.js-subject-anchor');
    this.flag = true;
    this.$diffiultyModal = $('.js-difficulty-modal');
    this.$scoreModal = $('.js-score-modal');
    this.scoreValidator = null;
    this.selectQuestion = [];
    this.questionOperate = null;
    this.$itemList = $('.js-item-list');
    this.optionCount = 0;
    this.editFieldId = 'question-stem-field';
    this.subjectItemValidator = null;
    this.init();
  }

  init() {
    // new showCkEditor();
    this.questionOperate = new QuestionOperate();
    // this.confirmFresh();
    this.sbListFixed();
    this.initEvent();
    this.initScoreValidator();
    this.setDifficulty();
    // this.showCkEditor();
  }

  confirmFresh() {
    $(window).on('beforeunload',function(){
      return Translator.trans('admin.block.not_saved_data_hint');
    });
  }

  initEvent() {
    this.$element.on('click', '.js-batch-select', event => this.batchToItem(event));
    this.$element.on('click', '.js-show-checkbox', event => this.itemToBatch(event));
    this.$element.on('click', '.js-batch-btn', event =>this.batchBtnClick(event));
    this.$element.on('click', '.js-finish-btn', event => this.finishBtnClick(event));
    this.$element.on('click', '*[data-anchor]', event => this.quickToQuestion(event, this.flag));
    this.$element.on('click', '.js-difficult-setting', event => this.showModal(event, this.$diffiultyModal));
    this.$element.on('click', '.js-score-setting', event => this.showScoreModal(event));
    this.$scoreModal.on('click', '.js-batch-score-confirm', event => this.batchSetScore(event));
    this.$itemList.on('click', '.js-item-edit', event => this.itemEdit(event));
    this.$itemList.on('click', '.js-finish-edit', event => this.finishEdit(event));
    this.$itemList.on('focus', '.js-item-stem-option-edit', event => this.editStemOrOption(event));
    this.$itemList.on('click', '.js-item-option-delete', event => this.deleteOption(event));
    this.$itemList.on('click', '.js-item-option-add', event => this.addOption(event));
  }

  sbListFixed() {
    let width = $('.js-subject-data').width();
    if (!this.$element.length) {
      return;
    }
    const self = this;
    let listTop = this.$element.offset().top;
    $(window).scroll(function(event) {
      self.$element.width(width);
      if ($(window).scrollTop() >= listTop) {
        self.$element.addClass('sb-fixed');
      } else {
        self.$element.removeClass('sb-fixed');
      }
    });
  }

  batchToItem(event) {
    if (event.currentTarget !== event.target) {
      return;
    }
    const $target = $(event.currentTarget);
    let checked = $target.hasClass('checked');
    if (checked) {
      this.$sbCheckbox.removeClass('checked');
    } else {
      this.$sbCheckbox.addClass('checked');
    }
  }

  itemToBatch(event) {
    if (event.currentTarget !== event.target) {
      return;
    }
    this.countNumber();
  }

  countNumber() {
    let itemLength = this.$sbCheckbox.length;
    const self = this;
    setTimeout(function(){
      let $checkBox = $('.js-subject-list-body').find('.checked');
      let itemCheckedLength = $checkBox.length;
      if (itemLength == itemCheckedLength) {
        self.$allBtn.addClass('checked');
      } else {
        self.$allBtn.removeClass('checked');
      }
    }, 100);
  }

  batchBtnClick(event) {
    const $target = $(event.target);
    $target.toggleClass('hidden');
    this.toggleClass();
    this.flag = false;
  }

  finishBtnClick(event) {
    this.$batchBtn.toggleClass('hidden');
    this.toggleClass();
    this.flag = true;
  }

  toggleClass() {
    this.$batchWrap.toggleClass('hidden');
    this.$sbCheckbox.toggleClass('hidden');
  }

  quickToQuestion(event, flag) {
    const $target = $(event.currentTarget);
    if (!flag) {
      $target.find('.js-show-checkbox').toggleClass('checked');
      this.countNumber();
    } else {
      const position = $($target.data('anchor')).offset();
      $(document).scrollTop(position.top);
    }
  }

  showModal(event, modal) {
    let stats = this.statChosedQuestion();
    let keys = Object.keys(stats);
    if (keys.length === 0) {
      cd.message({ type: 'danger', message: Translator.trans('请选择题目') });
      return;
    }
    let html = '';
    $.each(stats, function(index, statsItem){
      let tr = statsItem.count + '道' + statsItem.name + '，';
      html += tr;
    });
    html = html.substring(0, html.length - 1) + '。';

    modal.find('.js-select').html(html);

    modal.modal('show');
  }

  showScoreModal(event) {
    let stats = this.statChosedQuestion();

    let $missScoreField = $('.miss-score-field');

    if (!this.isTestpaper()) {
      $missScoreField.addClass('hidden');
    } else if (stats.hasOwnProperty('choice') || stats.hasOwnProperty('uncertain_choice')) {
      $missScoreField.removeClass('hidden');
    } else {
      $missScoreField.addClass('hidden');
    }

    if (this.scoreValidator != null) {
      this.scoreValidator.resetForm();
    }

    this.$scoreModal.find('input').each(function() {
      $(this).val('');
    });

    this.showModal(event, this.$scoreModal);
  }

  statChosedQuestion() {
    let stats = {};
    let self = this;

    self.$element.find('.js-show-checkbox.checked').each(function(){
      let type = $(this).data('type'),
        name = $(this).data('name'),
        order = $(this).data('order');

      if (typeof stats[type] == 'undefined') {
        stats[type] = {name:name, count:1};
      } else {
        stats[type]['count']++;
      }
      self.selectQuestion.push(order);
    });

    return stats;
  }

  initScoreValidator() {
    this.scoreValidator = $('#batch-set-score-form').validate({
      onkeyup: false,
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
        }
      },
      messages: {
        missScore: {
          noMoreThan: '漏选分值不得超过题目分值'
        }
      }
    });

    $.validator.addMethod( 'noMoreThan', function(value, element, param) {
      return value <= $(param).val();
    }, 'Please enter a lesser value.' );
  }

  initSubjectItemValidator() {
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

    this.subjectItemValidator = $('#subject-edit-item-form').validate({
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

  isTestpaper() {
    return ($('input[name="isTestpaper"]').val() == 1);
  }

  batchSetScore() {
    if (this.scoreValidator.form()) {
      let score = $('input[name="score"]').val();
      this.questionOperate.modifyScore(this.selectQuestion, score);
      this.selectQuestion = [];

      this.updateTotalScoreText();
      this.$scoreModal.modal('hide');
    }
  }

  setDifficulty() {
    let self = this;
    $('.js-difficulty-btn').click(function(){
      let difficulty = $('input[name=\'difficultyRadios\']:checked').val();
      let text = $('input[name=\'difficultyRadios\']:checked').next().text();
      self.questionOperate.modifyDifficulty(self.selectQuestion, difficulty, text);
      self.selectQuestion = [];
      cd.message({ type: 'success', message: Translator.trans('难度修改成功') });
      self.$diffiultyModal.modal('hide');
    });
  }

  itemEdit(event) {
    const $editItem = $('.subject-edit-item');
    if ($editItem.length !== 0) {
      let order = $editItem.find('.subject-edit-item__order').text();
      cd.message({
        type: 'warning',
        message: `请先完成第${order}题的编辑`,
      });
      return;
    }

    let $target = $(event.currentTarget);
    let url = $target.parents('.subject-item__operation').data('url');
    let $item = $target.parents('.subject-item');
    const token = $item.attr('id');
    let question = this.questionOperate.getQuestion(token);
    $.post(url, {question:{}}, html => {
      $item.addClass('hidden');
      $item.after(html);
      this.initSubjectItemValidator();
      this.editFieldId = 'question-stem-field';
      new showCkEditor({fieldId: this.editFieldId});
      // this.optionCount = question['options'].length;
      this.optionCount = 4;
    });
  }

  editStemOrOption(event) {
    const $input = $(event.currentTarget);
    const $textArea = $input.next();
    const fieldId = $textArea.attr('id');

    $input.addClass('hidden');
    if (fieldId === 'question-stem-field') {
      $('.js-upload-stem-attachment').removeClass('hidden');
    } else {
      $('.js-upload-stem-attachment').addClass('hidden');
    }

    new showCkEditor({fieldId: fieldId, oldFieldId: this.editFieldId});
    this.editFieldId = fieldId;
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

  finishEdit(event) {
    const $editItem = $(event.currentTarget).parents('.subject-edit-item');
    const $item = $('.subject-item.hidden');
    //todo 校验
    /**
     * 3. 多选题必须选择两个以上答案
     */
    this.subjectItemValidator.resetForm();
    if (this.subjectItemValidator.form()) {
      $editItem.remove();
      $item.removeClass('hidden');
    }
  }

  updateTotalScoreText() {
    if (this.isTestpaper()) {
      $('.js-total-score').text(`总分${this.totalScore}分`);
    }
  }
}

new sbList();
