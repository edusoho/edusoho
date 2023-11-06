import QuestionOperate from './operate';
import showEditor from './edit';
import { Browser } from 'common/utils';
import { debounce } from 'app/common/widget/debounce';

export default class sbList {
  constructor() {
    this.$element = $('.js-subject-list');
    this.$itemList = $('.js-item-list');
    this.$subjectData = $('.js-subject-data');
    this.$batchBtn = $('.js-batch-btn');
    this.$allBtn = $('.js-batch-select');
    this.flag = true;
    this.$diffiultyModal = $('.js-difficulty-modal');
    this.$scoreModal = $('.js-score-modal');
    this.$categoryModal = $('.js-category-modal');
    this.scoreValidator = null;
    this.selectQuestion = [];
    this.questionOperate = null;
    this.testpaperTitle = '';
    this.redirect = false;
    this.questionOperate = null;
    this.timer = false;
    this.questionBankId = $('#questionBankId').val();
    this.init();
  }

  init() {
    this.initQuestionOperate();
    this.confirmFresh();
    this.sbListFixed();
    this.footerButtonFixed();
    this.initEvent();
    this.initScoreValidator();
    this.setDifficulty();
    this.initTestpaperTitle();
    this.itemClick();
    this.statErrorQuestions();
    this.ieImg();
    this.initSelect();
    this.initTooltip();
  }

  initQuestionOperate() {
    let self = this;
    self.questionOperate = new QuestionOperate();
    self.questionOperate.on('correctQuestion', function(token) {
      if ($(`[data-anchor="#${token}"]`).hasClass('subject-list-item__num--error')) {
        $(`[data-anchor="#${token}"]`).removeClass('subject-list-item__num--error');
        self.statErrorQuestions();
      }
    });
    self.questionOperate.on('addQuestion', function(index, token, type) {
      $(`[data-anchor="#${index}"]`).attr('data-anchor', '#' + token);
      self.updateTotalScoreText();
      self.updateQuestionCountText(type);
    });
    self.questionOperate.on('deleteQuestion', function(type) {
      self.updateTotalScoreText();
      self.updateQuestionCountText(type);
    });
    self.questionOperate.on('updateQuestionScore', function(isTrigger = true) {
      self.updateTotalScoreText(isTrigger);
    });
    self.questionOperate.on('updateQuestionType', function(key, value, oldValue, token) {
      self.updateQuestionCountText(value);
      self.updateQuestionCountText(oldValue);
      self.questionTypeConvert(value, token);
    });
  }

  initSelect() {
    $('[name=categoryId]').select2({
      treeview: true,
      dropdownAutoWidth: true,
      treeviewInitState: 'collapsed',
      placeholderOption: 'first',
    });
  }

  initTooltip() {
    $('[data-toggle=tooltip]').tooltip();
  }

  confirmFresh() {
    let self = this;
    $(window).on('beforeunload',function(){
      if (!self.redirect) {
        return Translator.trans('admin.block.not_saved_data_hint');
      }
    });
  }

  initEvent() {
    this.$element.on('click', '.js-batch-select', event => this.batchToItem(event));
    this.$element.on('click', '.js-show-checkbox', event => this.itemToBatch(event));
    this.$element.on('click', '.js-batch-btn', event =>this.batchBtnClick(event));
    this.$element.on('click', '.js-finish-btn', event => this.finishBtnClick(event));
    this.$element.on('click', '*[data-anchor]', event => this.quickToQuestion(event, this.flag));
    this.$element.on('click', '.js-difficult-setting', event => this.showModal(event, this.$diffiultyModal));
    this.$element.on('click', '.js-category-setting', event => this.showModal(event, this.$categoryModal));
    this.$element.on('click', '.js-score-setting', event => this.showScoreModal(event));
    this.$scoreModal.on('click', '.js-batch-score-confirm', event => this.batchSetScore(event));
    this.$categoryModal.on('click', '.js-batch-set-category', event => this.batchSetCategory(event));
    this.$itemList.on('click', '.js-item-edit', event => this.itemEdit(event));
    this.$itemList.on('click', '.js-item-delete', event => this.deleteSubjectItem(event));
    this.$itemList.on('change', '.js-testpaper-title', event => this.editTestpaperTitle(event));
    this.$itemList.on('click', '.js-import-btn', event => this.finishImport(event));
  }

  initTestpaperTitle() {
    if (this.isTestpaper()) {
      this.testpaperTitle = $('.js-testpaper-title').val();
    }
    this.$itemList.on('click', '.subject-change-btn', event => this.itemConvert(event));
    this.$itemList.on('click', '.js-item-add', event => this.addModalShow(event, false));
    this.$itemList.on('click', '.js-item-add-sub', event => this.addModalShow(event, true));
    this.$subjectData.bind('change', '*[data-type]', (event, type) => this.updateQuestionCountText(type));
    this.$subjectData.bind('change', '.js-total-score', event => this.updateTotalScoreText());
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

  footerButtonFixed() {
    const self = this;
    window.addEventListener('scroll', debounce(self.scrollBottom, 100, true));
  }

  scrollBottom() {
    const $fixedFooterElement = $('.js-subject-item-btn');
    $(window).scroll(function(event) {
      const visibleBottom = parseInt(window.scrollY + document.documentElement.clientHeight);
      let footerBottom = 0;
      // 判断底部元素是否存在
      if ($('.es-footer-link').length) {
        footerBottom = parseInt($('.es-footer-link').offset().top);
      } else {
        if ($('.es-footer').length) {
          footerBottom = parseInt($('.es-footer').offset().top);
        }
      }
      // 其他主题默认滚动到距离底部560
      if (!footerBottom) {
        const scrollHeight = parseInt($(document).scrollTop());
        const windowHeight = parseInt($(document.body).height());
        const visibleHeight = parseInt($(window).height());
        const offsetHeight = windowHeight - 560;
        if ((scrollHeight + visibleHeight) < offsetHeight) {
          $fixedFooterElement.addClass('subject-bottom-fixed');
        } else {
          $fixedFooterElement.removeClass('subject-bottom-fixed');
        }
      } else {
        if (footerBottom > visibleBottom) {
          $fixedFooterElement.addClass('subject-bottom-fixed');
        } else {
          $fixedFooterElement.removeClass('subject-bottom-fixed');
        }
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
      this.$element.find('.js-show-checkbox').removeClass('checked');
    } else {
      this.$element.find('.js-show-checkbox').addClass('checked');
    }
  }

  itemToBatch(event) {
    if (event.currentTarget !== event.target) {
      return;
    }
    this.countNumber();
  }

  countNumber() {
    let itemLength = this.$element.find('.js-show-checkbox').length;
    const self = this;
    setTimeout(function() {
      let $checkBox = $('.js-subject-list-body').find('.checked');
      let itemCheckedLength = $checkBox.length;
      if (itemLength === itemCheckedLength) {
        self.$allBtn.addClass('checked');
      } else {
        self.$allBtn.removeClass('checked');
      }
    }, 100);
  }

  batchBtnClick(event) {
    if (this.isEditing()) {
      return;
    }
    const $target = $(event.target);
    $target.toggleClass('hidden');
    this.toggleClass(true);
    this.flag = false;
  }

  finishBtnClick(event) {
    this.$element.find('.js-show-checkbox').removeClass('checked');
    this.$allBtn.removeClass('checked');
    this.$batchBtn.toggleClass('hidden');
    this.toggleClass(false);
    this.flag = true;
  }

  toggleClass(isShow) {
    if (isShow) {
      this.$element.find('.js-subject-wrap').removeClass('hidden');
      this.$element.find('.js-show-checkbox').removeClass('hidden');
    } else {
      this.$element.find('.js-subject-wrap').addClass('hidden');
      this.$element.find('.js-show-checkbox').addClass('hidden');
    }
  }

  quickToQuestion(event, flag) {
    const $target = $(event.currentTarget);
    if (!flag) {
      $target.find('.js-show-checkbox').toggleClass('checked');
      this.countNumber();
    } else {
      const position = $($target.attr('data-anchor')).offset();
      $(document).scrollTop(position.top);
    }
  }

  showModal(event, modal) {
    if (this.isEditing()) {
      return ;
    }
    let stats = this.statChosedQuestion();
    let keys = Object.keys(stats);
    if (keys.length === 0) {
      cd.message({ type: 'danger', message: Translator.trans('subject.select_question_hint') });
      return;
    }
    let html = '';
    $.each(stats, function(index, statsItem){
      let tr = statsItem.count + Translator.trans('subject.question_unit') + statsItem.name + Translator.trans('subject.comma');
      html += tr;
    });
    html = html.substring(0, html.length - 1) + Translator.trans('subject.period');

    modal.find('.js-select').html(html);

    modal.modal('show');
  }

  showScoreModal(event) {
    if (this.isEditing()) {
      return ;
    }
    let stats = this.statChosedQuestion();
    let $missScoreField = $('.js-miss-score-field');

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
        name = $(this).parent().next('.js-type-name').text(),
        token = $(this).parents('.js-subject-anchor').data('anchor');

      if (typeof stats[type] === 'undefined') {
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
          max: 999,
          min: 0,
          es_score: true
        },
        missScore: {
          required: false,
          max: 999,
          min: 0,
          noMoreThan: '#score',
          es_score: true
        }
      },
      messages: {
        missScore: {
          noMoreThan: Translator.trans('subject.miss_score_no_more_than_score'),
        }
      }
    });

    $.validator.addMethod( 'noMoreThan', function(value, element, param) {
      if (value == '') {
        return true;
      } else {
        return parseFloat(value) <= parseFloat($(param).val());
      }
    }, 'Please enter a lesser value.' );
  }

  isTestpaper() {
    return ($('input[name="isTestpaper"]').val() == 1);
  }

  batchSetScore() {
    if (this.scoreValidator.form()) {
      let score = this.$scoreModal.find('input[name="score"]').val();
      let missScore = this.$scoreModal.find('input[name="missScore"]').val();
      let scoreObj = {
        score: score,
        missScore: missScore,
      };
      this.questionOperate.modifyScore(this.selectQuestion, scoreObj, this.isTestpaper());
      this.selectQuestion = [];

      cd.message({ type: 'success', message: Translator.trans('subject.score_update_success') });
      this.$scoreModal.modal('hide');
    }
  }

  batchSetCategory() {
    let categoryId = this.$categoryModal.find('#categoryId').val();
    let categoryText = this.$categoryModal.find('#categoryId').find('option:selected').text();
    this.questionOperate.modifyCategory(this.selectQuestion, categoryId, $.trim(categoryText));
    this.selectQuestion = [];

    cd.message({ type: 'success', message: Translator.trans('subject.category_update_success') });
    this.$categoryModal.modal('hide');
  }

  setDifficulty() {
    let self = this;
    $('.js-difficulty-btn').click(function() {
      let difficulty = $('input[name=\'difficultyRadios\']:checked').val();
      let text = $('input[name=\'difficultyRadios\']:checked').next().text();
      self.questionOperate.modifyDifficulty(self.selectQuestion, difficulty, text);
      self.selectQuestion = [];
      cd.message({ type: 'success', message: Translator.trans('subject.difficulty_update_success') });
      self.$diffiultyModal.modal('hide');
    });
  }

  itemEdit(event) {
    if (this.isEditing()) {
      return;
    }

    let self = this;
    let seq = 0;
    let token = '';
    let question = {};
    let isSub = 0;
    let $target = $(event.currentTarget);
    let $item = $target.parents('.subject-item');
    let url = $target.parents('.subject-item__operation').data('url');
    if (typeof $item.attr('data-material-token') !== 'undefined') {
      seq = $item.data('key');
      token = $item.attr('data-material-token');
      question = this.questionOperate.getSubQuestion(token, seq);
      isSub = 1;
      seq++;
    } else {
      seq = this.questionOperate.getQuestionOrder($item.attr('id'));
      question = this.questionOperate.getQuestion($item.attr('id'));
      token = $item.attr('id');
    }

    let data = {
      'seq' : seq,
      'token' : token,
      'question' : question,
      'isSub' : isSub,
      'method' : 'edit',
      'isTestpaper': this.isTestpaper() ? 1 : 0,
      'questionBankId': this.questionBankId,
    };
    $.post(url, data, html => {
      $item.replaceWith(html);
      showEditor.getEditor(question['type'], $('.js-edit-form'), self.questionOperate);
      self.initSelect();
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
    if ($('.js-attachment-list').length > 0) {
      let attachment = {};
      if ($('.js-attachment-list-stem').find('.js-attachment-name').length > 0) {
        attachment['stem'] = {
          'type' : 'attachment',
          'targetType' : 'question.stem',
          'fileIds' : $('.js-attachment-ids-stem').val(),
          'fileName' : $('.js-attachment-list-stem').find('.js-attachment-name').text()
        };
      }

      if ($('.js-attachment-list-analysis').find('.js-attachment-name').length > 0) {
        attachment['analysis'] = {
          'type' : 'attachment',
          'targetType' : 'question.analysis',
          'fileIds' : $('.js-attachment-ids-analysis').val(),
          'fileName' : $('.js-attachment-list-analysis').find('.js-attachment-name').text()
        };
      }
      question['attachment'] = attachment;
    }

    let seq = $('.js-edit-form-seq').text();
    let isSub = $('.js-sub-judge').val();
    let method = $('.js-hidden-method').val();
    let data = {
      'seq' : seq,
      'token' : question.token,
      'question' : question,
      'fromType' : fromType,
      'toType' : toType,
      'isSub' : isSub,
      'method' : method,
      'isTestpaper': this.isTestpaper() ? 1 : 0,
      'questionBankId': this.questionBankId,
    };
    data.fromType = fromType;
    data.toType = toType;
    let self  = this;
    $.post(url, data, html => {
      $item.replaceWith(html);
      showEditor.getEditor(toType, $('.js-edit-form'), self.questionOperate);
    });
  }

  itemClick() {
    let self = this;
    $('.js-create-btn').on('click', (event) => {
      const $target = $(event.target);
      if (self.timer) clearTimeout(self.timer);
      self.timer = setTimeout(() => {
        $target.attr('disabled', true);
        const $modal = $('#cd-modal');
        const isSubCreate = $modal.attr('data-sub');
        const url = $target.data('url');
        let token = $modal.attr('data-index');
        let type = $target.data('type');
        if (isSubCreate == 'false') {
          self.itemAdd(token, type, url, $target);
        } else {
          self.subItemAdd(token, type, url);
        }
      }, 500);
    });
  }

  itemAdd(token, type, url, $target) {
    let self = this;
    let seq = self.questionOperate.getQuestionOrder(token) + 1;
    let isTestpaper = this.isTestpaper() ? 1 : 0;
    let data = {
      'seq' : seq,
      'token' : token,
      'method' : 'add',
      'isTestpaper' : isTestpaper,
      'questionBankId': this.questionBankId,
    };
    $.post(url, data).then((res) => {
      $('#cd-modal').modal('hide');
      $('.js-create-btn').attr('disabled', false);
      let index = seq + 1;
      self.orderQuestionList(index, $(`[data-anchor="#${token}"]`).parent(), $(`#${token}`));
      $(`[data-anchor="#${token}"]`).parent().after(self.getNewListItem(seq, type, $target.text()));
      self.statErrorQuestions();
      let nextItem = $(`#${token}`).next('.subject-item');
      if (nextItem.hasClass('subject-sub-item')) {
        $(`[data-material-token="${token}"]`).last().after(res);
      } else {
        $(`#${token}`).after(res);
      }
      showEditor.getEditor(type, $('.js-edit-form'), self.questionOperate);
    });
  }

  subItemAdd(token, type, url) {
    let self = this;
    let materialQuestion = this.questionOperate.getQuestion(token);
    let seq = materialQuestion['subQuestions'].length + 1;
    let isTestpaper = this.isTestpaper() ? 1 : 0;
    let data = {
      'seq' : seq,
      'token' : token,
      'method' : 'add',
      'isSub' : 1,
      'isTestpaper' : isTestpaper,
      'questionBankId': this.questionBankId,
    };
    $.post(url, data).then((res) => {
      $('#cd-modal').modal('hide');
      $('.js-create-btn').attr('disabled', false);
      let nextItem = $(`#${token}`).next('.subject-item');
      if (nextItem.hasClass('subject-sub-item')) {
        $(`[data-material-token="${token}"]`).last().after(res);
      } else {
        $(`#${token}`).after(res);
      }
      showEditor.getEditor(type, $('.js-edit-form'), self.questionOperate);
    });
  }

  getNewListItem(seq, type, typeName) {
    if (type !== 'material'){
      return `<div class="col-sm-3 subject-list-item js-subject-list-item">
          <div class="subject-list-item__num  js-subject-anchor" data-anchor="#${seq}">
            <span class="js-list-index">${seq}</span>
            <label class="sb-checkbox cd-checkbox js-show-checkbox hidden" data-type="${type}"><input type="checkbox" data-toggle="cd-checkbox"></label>
          </div>
          <span class="js-type-name">${typeName}</span>
        </div>`;
    } else{
      return `<div class="col-sm-3 subject-list-item js-subject-list-item">
          <div class="subject-list-item__num  js-subject-anchor" data-anchor="#${seq}">
            <span class="js-list-index">${seq}</span>
          </div>
          <span class="js-type-name">${typeName}</span>
        </div>`;
    }

  }

  deleteSubjectItem(event) {
    if (this.isEditing()) {
      return;
    }
    let tokenList = this.questionOperate.getTokenList();
    if (tokenList.length < 2) {
      cd.message({
        type : 'danger',
        message : Translator.trans('subject.question_delete_tip'),
      });
      return;
    }
    cd.confirm({
      title: Translator.trans('subject.delete.title'),
      content: Translator.trans('subject.delete.content'),
      okText: Translator.trans('site.confirm'),
      cancelText: Translator.trans('site.cancel'),
    }).on('ok', () => {
      const $item = $(event.currentTarget).parent().parent();
      let token = $item.attr('id');

      if ($item.hasClass('subject-sub-item')) {
        token = $item.attr('data-material-token');
        let key = $item.attr('data-key');
        this.questionOperate.deleteSubQuestion(token, key);
        $item.remove();
        this.changeBottomFixed();
        let order = 0;
        $(`[data-material-token="${token}"]`).each(function() {
          $(this).attr('id', `sub${order}`);
          $(this).attr('data-key', order++);
          $(this).find('.subject-sub-item__number').text(`(${order})`);
        });

        return;
      }

      let question = this.questionOperate.getQuestion(token);
      let order = this.questionOperate.getQuestionOrder(token);
      const $listItem = $(`[data-anchor=#${token}]`).parent();
      this.orderQuestionList(order, $listItem, $item);
      this.questionOperate.deleteQuestion(token);
      const self = this;
      if (question.type === 'material') {
        $item.nextUntil('.js-subject-main-item').each(function() {
          $(this).remove();
          self.changeBottomFixed();
        });
      }
      $listItem.remove();
      $item.remove();
      self.changeBottomFixed();

      this.statErrorQuestions();
    });
  }

  changeBottomFixed() {
    const visibleBottom = parseInt(window.scrollY + document.documentElement.clientHeight);
    let footerBottom = 0;
    // 判断底部元素是否存在
    if ($('.es-footer-link').length) {
      footerBottom = parseInt($('.es-footer-link').offset().top);
    } else {
      if ($('.es-footer').length) {
        footerBottom = parseInt($('.es-footer').offset().top);
      }
    }
    // 适配其他主题
    if (!footerBottom) {
      const scrollHeight = parseInt($(document).scrollTop());
      const windowHeight = parseInt($(document.body).height());
      const visibleHeight = parseInt($(window).height());
      const offsetHeight = windowHeight - 560;
      if ((scrollHeight + visibleHeight) >= offsetHeight) {
        $('.js-subject-item-btn').removeClass('subject-bottom-fixed');
      }
    } else {
      if (footerBottom < visibleBottom) {
        $('.js-subject-item-btn').removeClass('subject-bottom-fixed');
      }
    }
  }

  editTestpaperTitle(event) {
    let val = $(event.target).val();

    if ($.trim(val) === '') {
      cd.message({
        type: 'danger',
        message: Translator.trans('subject.testpaper_title_empty_hint'),
      });
      $(event.target).val(this.testpaperTitle);
      return;
    }

    let length = val.length;
    for (let i = 0; i < val.length; i++) {
      if (val.charCodeAt(i) > 127) {
        length++;
      }
    }

    if (length > 50) {
      cd.message({
        type: 'danger',
        message: Translator.trans('subject.testpaper_title_too_long_hint'),
      });
      $(event.target).val(this.testpaperTitle);
      return;
    }

    this.testpaperTitle = val;
  }

  updateQuestionCountText(type) {
    let totalCount = this.questionOperate.getQuestionCount('total');
    let typeCount = this.questionOperate.getQuestionCount(type);
    $('.js-total-num').text(Translator.trans('subject.question_count', {count: totalCount}));
    $(`[data-type=${type}]`).find('.subject-data__num').text(Translator.trans('subject.question_count', {count: typeCount}));
  }

  updateTotalScoreText(isTrigger = true) {
    if (this.isTestpaper() && isTrigger) {
      let totalScore = parseFloat(this.questionOperate.getTotalScore()).toFixed(1);
      $('.js-total-score').text(Translator.trans('subject.total_score', {totalScore: totalScore}));
    }
  }

  orderQuestionList(seq, $listItem, $subjectItem) {
    let listSeq = seq;
    let itemSeq = seq;
    $listItem.nextAll('.subject-list-item').each(function() {
      $(this).find('.js-list-index').text(listSeq);
      listSeq++;
    });

    $subjectItem.nextAll('.subject-item').not('.subject-sub-item').each(function() {
      $(this).find('.subject-item__number').text(itemSeq);
      itemSeq++;
    });
  }

  finishImport(event) {
    if (this.isEditing()) {
      return;
    }
    let hasError = false;
    let self = this;
    let errorTip = '';
    this.$element.find('.subject-list-item__num--error').each(function () {
      errorTip = errorTip + $(this).find('.js-list-index').text() + '、';
      hasError = true;
    });
    errorTip = Translator.trans('subject.question_error_tip', {seqs: errorTip.substring(0, errorTip.length - 1)});
    if (hasError) {
      cd.message({
        type : 'danger',
        message : errorTip
      });
      return ;
    }

    if (!this.isMaterialHasSub()) {
      return;
    }

    let title = this.isTestpaper() ? this.testpaperTitle : '';
    const $target = $(event.currentTarget);
    $target.button('loading');
    $.ajax({
      type: "POST",
      url: $target.data('url'),
      data: JSON.stringify({title: title, questions: this.questionOperate.getQuestions()}),
      dataType: "json",
      contentType: "application/json;charset=utf-8",
      success: function(resp) {
        if (resp === true) {
          $target.button('reset');
          cd.message({
            type : 'success',
            message : Translator.trans('subject.save_success'),
          });
          self.redirect = true;
          window.location.href = $target.data('redirectUrl');
        }
      }
    });
  }

  isMaterialHasSub() {
    let self = this;
    let tokenList = self.questionOperate.getTokenList();
    for (let i = 0;i < tokenList.length;i++) {
      let token = tokenList[i];
      let question = self.questionOperate.getQuestion(token);
      if (question['type'] === 'material' && question['subQuestions'].length === 0) {
        let seq = self.questionOperate.getQuestionOrder(token);
        cd.message({
          type : 'danger',
          message : Translator.trans('subject.material_nosub_tip', {seq: seq})
        });
        return false;
      }
    }

    return true;
  }

  statErrorQuestions() {
    let errorTip = '';
    let isShow = false;
    this.$element.find('.subject-list-item__num--error').each(function () {
      errorTip = errorTip + $(this).find('.js-list-index').text() + '、';
      isShow = true;
    });
    errorTip = Translator.trans('subject.question_error_tip', {seqs: errorTip.substring(0, errorTip.length - 1)});
    if (isShow) {
      $('.js-error-tip').html(errorTip);
    } else {
      $('.js-error-tip').html('');
    }
  }

  static _serializeArrayConvertToJson(data){
    let serializeObj={};
    for (let item of data) {
      if (serializeObj[item.name]) {
        if (!serializeObj[item.name].push) {
          serializeObj[item.name] = [serializeObj[item.name]];
        }
        serializeObj[item.name].push(item.value);
      } else {
        serializeObj[item.name] = item.value;
      }
    }
    return serializeObj;
  }

  addModalShow(event, isAddSub) {
    if (this.isEditing()) {
      return;
    }

    let $target = $(event.currentTarget);
    let $modal = $('#cd-modal');
    let token = $target.closest('.js-subject-item').attr('id');
    if (isAddSub) {
      $modal.find('[data-type="material"]').addClass('hidden');
      $modal.attr('data-sub', 'true');
    } else {
      $modal.find('[data-type="material"]').removeClass('hidden');
      $modal.attr('data-sub', 'false');
    }
    $modal.attr('data-index', token);
    $modal.modal('show');
  }
  
  isEditing() {
    const $editItem = $('.js-subject-edit-item');
    const isSub = $editItem.hasClass('js-sub-edit-item');
    let parentNum = '';
    if ($editItem.length !== 0) {
      const seq = $editItem.find('.js-edit-form-seq').text();
      if (isSub) {
        const id = $editItem.find('.js-hidden-token').val();
        const parentSeq = $(`#${id}`).find('.js-subject-item-number').text();
        parentNum = $.trim(parentSeq);
      }
      const message = isSub ? Translator.trans('subject.sub_is_editing_warning', {parentNum:parentNum, seq:seq}) :Translator.trans('subject.is_editing_warning', {seq: seq});
      cd.message({
        type: 'warning',
        message: message,
      });
      return true;
    }

    return false;
  }

  questionTypeConvert(type, token) {
    let $list = $('.js-subject-list').find(`[data-anchor=#${token}]`);
    $list.find('.js-show-checkbox').attr('data-type', type);
    $list.next('.js-type-name').text(this.getTypeName(type));
  }

  getTypeName(type) {
    switch (type) {
    case 'single_choice':
      return Translator.trans('course.question.type.single_choice');
    case 'uncertain_choice':
      return Translator.trans('course.question.type.uncertain_choice');
    case 'choice':
      return Translator.trans('course.question.type.choice');
    case 'determine':
      return Translator.trans('course.question.type.determine');
    case 'essay':
      return Translator.trans('course.question.type.essay');
    case 'fill':
      return Translator.trans('course.question.type.fill');
    case 'material':
      return Translator.trans('course.question.type.material');
    default:
      return Translator.trans('course.question.type.unknown');
    }
  }

  ieImg() {
    if (Browser.ie10 || Browser.ie11) {
      this.$itemList.addClass('ie-item-list');
    }
  }
}

new sbList();
