import QuestionOperate from './operate';

export default class sbList {
  constructor() {
    this.$element = $('.js-subject-list');
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
    this.totalScore = 0;
    this.totalNum = 0;
    this.selectQuestion = [];
    this.questionOperate = null;
    this.$itemList = $('.js-item-list');
    this.init();
  }

  init() {
    this.questionOperate = new QuestionOperate();
    this.confirmFresh();
    this.sbListFixed();
    this.initEvent();
    this.initScoreValidator();
    this.initTotalScore();
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
    // this.$itemList.on('click', '.js-subject-item-edit', event => this.editSubjectItem(event));
    // this.$itemList.on('click', '.js-finish-edit', event => this.finishEdit(event));
    this.$itemList.on('click', '.js-subject-item-delete', event => this.deleteSubjectItem(event));
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
    this.$anchor.addClass('sb-cursor-default');
    this.flag = false;
  }


  finishBtnClick(event) {
    this.$batchBtn.toggleClass('hidden');
    this.toggleClass();
    this.$anchor.removeClass('sb-cursor-default');
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

  isTestpaper() {
    return ($('input[name="isTestpaper"]').val() == 1);
  }

  initTotalScore() {

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

  editSubjectItem(event) {
    const $item = $(event.currentTarget).parent().parent();

    let type = 'choice';

    $.ajax({
      type: 'post',
      url: `/subject/edit/${type}`,
      data: {
        //题目对象
      },
    }).done(function(resp) {
      $item.addClass('hidden');
      $item.after($(resp));
    });
  }

  finishEdit(event) {
    const $editItem = $(event.currentTarget).parent().parent();
    const $item = $('.subject-item.hidden');
    $editItem.remove();
    $item.removeClass('hidden');
  }

  deleteSubjectItem(event) {
    cd.confirm({
      title: '确认删除',
      content: '确定要删除这道题目吗?',
      okText: '确定',
      cancelText: '取消',
    }).on('ok', () => {
      const question = {};
      const $item = $(event.currentTarget).parent().parent();

      //todo: 修改总分
      if (question.type == 'material') {
      } else {
      }

      this.updateTotalScoreText();

      if ($item.hasClass('subject-sub-item')) {
        //todo: 更新子题序号
        $item.remove();
        return;
      }

      const itemId = $item.attr('id');
      const $listItem = $(`[data-anchor=#${itemId}]`).parent();

      let curItemId = itemId;
      $item.nextAll('.subject-item').not('.subject-sub-item').each(function() {
        $(this).attr('id', curItemId).find('.subject-item__number').text(curItemId);
        curItemId++;
      });

      curItemId = itemId;
      $listItem.nextAll('.subject-list-item').each(function() {
        $(this).find('.subject-list-item__num').attr('data-anchor', `#${curItemId}`).text(curItemId)
            .find('.sb-checkbox').attr('data-order', curItemId);
        curItemId++;
      });

      //todo: 更新题型数量
      this.totalNum -= 1;
      $('.js-total-num').text(`共${this.totalNum}道题`);

      //todo: 如果是材料题同时删除子题
      $listItem.remove();
      $item.remove();
    });
  }

  updateTotalScoreText() {
    if (this.isTestpaper()) {
      $('.js-total-score').text(`总分${this.totalScore}分`);
    }
  }
}

new sbList();
