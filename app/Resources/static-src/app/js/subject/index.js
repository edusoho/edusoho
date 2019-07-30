import QuestionOperate from './operate';
import showEditor from './edit';

export default class sbList {
  constructor() {
    this.$element = $('.js-subject-list');
    this.$itemList = $('.js-item-list');
    this.$subjectData = $('.js-subject-data');
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
    this.testpaperTitle = '';
    this.redirect = false;
    this.init();
  }

  init() {
    this.questionOperate = new QuestionOperate();
    this.confirmFresh();
    this.sbListFixed();
    this.initEvent();
    this.initScoreValidator();
    this.setDifficulty();
    this.initTestpaperTitle();
    this.itemClick();
    this.statErrorQuestions();
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
    this.$element.on('click', '.js-score-setting', event => this.showScoreModal(event));
    this.$scoreModal.on('click', '.js-batch-score-confirm', event => this.batchSetScore(event));
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
    this.$sbCheckbox.removeClass('checked');
    this.$allBtn.removeClass('checked');
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
        name = $(this).parent().next('.js-type-name').text(),
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
      let missScore = $('input[name="missScore"]').val();
      let scoreObj = {
        score: score,
        missScore: missScore,
      };
      this.questionOperate.modifyScore(this.selectQuestion, scoreObj, this.isTestpaper());
      this.selectQuestion = [];

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
    if (typeof $item.attr('data-material-token') != 'undefined') {
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
    };
    $.post(url, data, html=> {
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
      const isSubCreate = $('#cd-modal').attr('data-sub');
      const url = $target.data('url');
      let token = $('#cd-modal').attr('data-index');
      let type = $target.data('type');
      if (isSubCreate == 'false') {
        self.itemAdd(token, type, url, $target);
      } else {
        self.subItemAdd(token, type, url);
      }
    });
  }

  itemAdd(token, type, url, $target) {
    let self = this;
    let seq = self.questionOperate.getQuestionOrder(token) + 1;
    $.post(url, {'seq' : seq, 'token' : token, 'method' : 'add'}).then((res) => {
      $('#cd-modal').modal('hide');
      let index = seq + 1;
      self.orderQuestionList(index, $(`[data-anchor="#${token}"]`).parent(), $(`#${token}`));
      $(`[data-anchor="#${token}"]`).parent().after(self.getNewListItem(seq, type, $target.text()));
      self.statErrorQuestions();
      var nextItem = $(`#${token}`).next('.subject-item');
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
    $.post(url, {'seq' : seq, 'token' : token, 'method' : 'add', 'isSub' : 1}).then((res) => {
      $('#cd-modal').modal('hide');
      var nextItem = $(`#${token}`).next('.subject-item');
      if (nextItem.hasClass('subject-sub-item')) {
        $(`[data-material-token="${token}"]`).last().after(res);
      } else {
        $(`#${token}`).after(res);
      }
      showEditor.getEditor(type, $('.js-edit-form'), self.questionOperate);
    });
  }

  getNewListItem(seq, type, typeName) {
    return `<div class="col-sm-3 subject-list-item">
      <div class="subject-list-item__num  js-subject-anchor" data-anchor="#${seq}">
        <span class="js-list-index">${seq}</span>
        <label class="sb-checkbox cd-checkbox js-show-checkbox hidden" data-type="${type}"><input type="checkbox" data-toggle="cd-checkbox"></label>
      </div>
      <span class="js-type-name">${typeName}</span>
    </div>`;
  }

  deleteSubjectItem(event) {
    if (this.isEditing()) {
      return;
    }
    cd.confirm({
      title: '确认删除',
      content: '确定要删除这道题目吗?',
      okText: '确定',
      cancelText: '取消',
    }).on('ok', () => {
      const $item = $(event.currentTarget).parent().parent();
      let token = $item.attr('id');

      if ($item.hasClass('subject-sub-item')) {
        token = $item.attr('data-material-token');
        let key = $item.attr('data-key');
        console.log(key);
        console.log(token);
        let order = $item.find('.subject-sub-item__number').text().replace(/[^0-9]/ig, '');
        $item.nextUntil('.js-subject-main-item').each(function() {
          $(this).find('.subject-sub-item__number').text(`(${order})`);
          order++;
        });
        this.questionOperate.deleteSubQuestion(token, key);
        $item.remove();
        return;
      }

      let question = this.questionOperate.getQuestion(token);
      let order = this.questionOperate.getQuestionOrder(token);
      const $listItem = $(`[data-anchor=#${token}]`).parent();
      this.orderQuestionList(order, $listItem, $item);
      this.questionOperate.deleteQuestion(token);

      if (question.type == 'material') {
        $item.nextUntil('.js-subject-main-item').each(function() {
          $(this).remove();
        });
      }
      $listItem.remove();
      $item.remove();
      this.statErrorQuestions();
    });
  }

  editTestpaperTitle(event) {
    let val = $(event.target).val();

    if ($.trim(val) == '') {
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
    $('.js-total-num').text(`共${totalCount}道题`);
    $(`[data-type=${type}]`).find('.subject-data__num').text(`共${typeCount}道题`);
  }

  updateTotalScoreText() {
    let totalScore = parseInt(this.questionOperate.getTotalScore());
    if (this.isTestpaper()) {
      $('.js-total-score').text(`总分${totalScore}分`);
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
    let hasError = false;
    let self = this;
    let errorTip = '第';
    this.$element.find('.subject-list-item__num--error').each(function () {
      errorTip = errorTip + $(this).find('.js-list-index').text() + '、';
      hasError = true;
    });
    errorTip = errorTip.substring(0, errorTip.length - 1) + '题有违规';
    if (hasError) {
      cd.message({
        type : 'danger',
        message : errorTip
      });
      return ;
    }

    let title = '';
    if (this.isTestpaper()) {
      title = this.testpaperTitle;
    }
    $.post($(event.currentTarget).data('url'), {title: title, questions: this.questionOperate.getQuestions()}, function(resp) {
      if (resp === true) {
        cd.message({
          type : 'success',
          message : '保存成功！',
        });
        self.redirect = true;
        window.location.href = $(event.currentTarget).data('redirectUrl');
      }
    });
  }

  statErrorQuestions() {
    let errorTip = '第';
    let isShow = false;
    this.$element.find('.subject-list-item__num--error').each(function () {
      errorTip = errorTip + $(this).find('.js-list-index').text() + '、';
      isShow = true;
    });
    errorTip = errorTip.substring(0, errorTip.length - 1) + '题有违规';
    if (isShow) {
      $('.js-error-tip').html(errorTip);
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
    const $editItem = $('.subject-edit-item');
    if ($editItem.length !== 0) {
      let seq = this.$itemList.find('.subject-edit-item').find('.js-edit-form-seq').text();
      cd.message({
        type: 'warning',
        message: `请先完成第${seq}题的编辑`,
      });
      return true;
    }

    return false;
  }
}

new sbList();
