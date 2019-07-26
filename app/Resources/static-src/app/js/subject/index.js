import QuestionOperate from './operate';
import showEditor from './edit';

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
    this.init();
  }

  init() {
    this.questionOperate = new QuestionOperate();
    //this.confirmFresh();
    this.sbListFixed();
    this.initEvent();
    this.initScoreValidator();
    this.setDifficulty();
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
    this.$itemList.on('click', '.js-item-delete', event => this.deleteSubjectItem(event));
    this.$itemList.on('click', '.subject-change-btn', event => this.itemConvert(event));
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
        token = $(this).parents('.js-subject-anchor').data('anchor');

      if (typeof stats[type] == 'undefined') {
        stats[type] = {name:name, count:1};
      } else {
        stats[type]['count']++;
      }
      self.selectQuestion.push(token.substr(1));
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

  isTestpaper() {
    return ($('input[name="isTestpaper"]').val() == 1);
  }

  batchSetScore() {
    if (this.scoreValidator.form()) {
      let score = $('input[name="score"]').val();
      this.questionOperate.modifyScore(this.selectQuestion, score);
      this.selectQuestion = [];

      this.updateTotalScoreText();
      cd.message({ type: 'success', message: Translator.trans('分数修改成功') });
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
    let $target = $(event.currentTarget);
    let $item = $target.parents('.subject-item');
    let seq = this.questionOperate.getQuestionOrder($item.attr('id'));
    if ($editItem.length !== 0) {
      cd.message({
        type: 'warning',
        message: `请先完成第${seq}题的编辑`,
      });
      return;
    }

    let self = this;
    let url = $target.parents('.subject-item__operation').data('url');
    let question = this.questionOperate.getQuestion($item.attr('id'));
    $.post(url, {seq: seq, question: question, token: $item.attr('id')}, html=> {
      $item.replaceWith(html);
      showEditor.getEditor(question['type'], $('.js-edit-form'), self.questionOperate);
    });
  }

  itemConvert(event) {
    let $target = $(event.currentTarget);
    let $item = $target.parents('.subject-item');
    let toType = $target.data('type');
    let fromType = $target.parents('.subject-change-list').data('fromType');
    let $form = $target.parents('.js-edit-form');
    let url = $target.parents('.subject-change-list').data('convertUrl');
    let question = sbList._serializeArrayConvertToJson($form.serializeArray());
    let seq = this.questionOperate.getQuestionOrder(question.token);
    let data = {
      "seq" : seq,
      "token" : question.token,
      "question" : question,
      "fromType" : fromType,
      "toType" : toType,
    };
    data.fromType = fromType;
    data.toType = toType;
    let self  = this;
    $.post(url, data, html => {
      $item.replaceWith(html);
      showEditor.getEditor(toType, $('.js-edit-form'), self.questionOperate);
    });
    console.log(data);


  }

  deleteSubjectItem(event) {
    cd.confirm({
      title: '确认删除',
      content: '确定要删除这道题目吗?',
      okText: '确定',
      cancelText: '取消',
    }).on('ok', () => {
      const $item = $(event.currentTarget).parent().parent();
      const token = $item.attr('id');
      let question = this.questionOperate.getQuestion(token);

      if ($item.hasClass('subject-sub-item')) {
        let order = $item.find('.subject-sub-item__number').text().replace(/[^0-9]/ig, '');
        $item.nextUntil('[class="subject-item"]').each(function() {
          $(this).find('.subject-sub-item__number').text(`(${order})`);
          order++;
        });
        this.questionOperate.deleteQuestion(token);
        this.updateTotalScoreText();
        $item.remove();
        return;
      }

      let order = this.questionOperate.getQuestionOrder(token);
      $item.nextAll('.subject-item').not('.subject-sub-item').each(function() {
        $(this).find('.subject-item__number').text(order);
        order++;
      });

      order = this.questionOperate.getQuestionOrder(token);
      const $listItem = $(`[data-anchor=#${token}]`).parent();
      $listItem.nextAll('.subject-list-item').each(function() {
        $(this).find('.subject-list-item__num').text(order)
            .find('.sb-checkbox').attr('data-order', order);
        order++;
      });

      this.questionOperate.deleteQuestion(token);
      this.updateQuestionCountText(question['type']);
      this.updateTotalScoreText();

      if (question.type == 'material') {
        $.each(question['subQuestions'], function(token, subQuestion) {
          $(`#${token}`).remove();
        });
      }
      $listItem.remove();
      $item.remove();
    });
  }

  updateQuestionCountText(type) {
    let totalCount = this.questionOperate.getQuestionCount('total');
    let typeCount = this.questionOperate.getQuestionCount(type);
    $('.js-total-num').text(`共${totalCount}道题`);
    $(`[data-type=${type}]`).find('.subject-data__num').text(`共${typeCount}道题`);
  }

  updateTotalScoreText() {
    let totalScore = this.questionOperate.getTotalScore();
    if (this.isTestpaper()) {
      $('.js-total-score').text(`总分${totalScore}分`);
    }
  }

  static _serializeArrayConvertToJson(data){
    let serializeObj={};
    for (let item of data) {
      if (serializeObj[item.name]) {
        if (!serializeObj[item.name].push) {
          serializeObj[item.name] = [serializeObj[item.name]];
        }
        serializeObj[item.name].push(item.value)
      } else {
        serializeObj[item.name] = item.value;
      }
    }
    return serializeObj;
  }
}

new sbList();
