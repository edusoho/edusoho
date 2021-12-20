import BatchSelect from '../../../../common/widget/batch-select';
import 'jquery-sortable';

class TestpaperForm {
  constructor($form) {
    this.$form = $form;
    this.$description = this.$form.find('[name="description"]');
    this.$questionForm = $('#testpaper-items-manager');
    this.validator = null;
    this.difficultySlider = null;
    this.scoreSlider = null;
    this.$scoreModal = $('.js-score-modal');
    this.$modal = $('#testpaper-confirm-modal');
    this.sections = [];
    this.questionsCount = 0;
    this.$score = null;
    this.$typeNav = this.$form.find('#testpaper-question-nav');
    new BatchSelect(this.$questionForm);
    this._initEvent();
    this._initValidate();
    this._initScoreValidator();
    this._initTypeSort();
  }

  _initEvent() {

    this.$form.on('click', '.js-request-save', event => this._confirmSave(event));
    this.$modal.on('click','.js-confirm-submit', event => this._submitSave(event));
    this.$typeNav.on('click', 'li', event => this._changeNav(event));
    this.$form.on('click', '[data-role="item-delete-btn"]', event => this.deleteQuestion(event));
    this.$form.on('click', '[data-role="batch-delete-btn"]', event => this.batchDelete(event));
    this.$form.on('click', '[data-role="set-score-btn"]', event => this.showScoreModal(event));
    this.$form.on('click', '.js-pick-modal', event => this.showPickModal(event));
    this.$form.on('lengthChange','[data-role="question-body"]', event => this.changeQuestionCount(event));
    this.$scoreModal.on('click', '.js-batch-score-confirm', event => this.batchSetScore(event));
    $('.modal').on('selectQuestion', (event, typeQuestions) => this.selectQuestion(event, typeQuestions));
    this.initSortList();
    this.initTestpaperScore();
  }

  initTestpaperScore() {
    this.$score = $('#testpaper-items-manager').find('.score-validate');
    this.$score.on('blur', event => this._processScore(event));
  }

  _processScore(event) {
    let $target = $(event.target);

    this._validateQuestionScore($target);

    return false;
  }

  _confirmSave() {
    let status = this.validator.form();

    if (!status) {
      return;
    }

    this.questionsCount = 0;
    this.questions = [];
    this.sections = [];
    let stats = this._calTestpaperStats();

    let html = '';
    $.each(stats, function(index, statsItem){
      let tr = '<tr>';
      tr += `<td>${statsItem.name}</td>`;
      tr += `<td>${statsItem.count}</td>`;
      tr += `<td>${statsItem.score.toFixed(1)}</td>`;
      tr += '</tr>';
      html += tr;
    });

    this.$modal.find('.detail-tbody').html(html);

    this.$modal.modal('show');
  }

  _calTestpaperStats() {
    let stats = {};
    let self = this;
    let seq = 1;
    this.$typeNav.find('li').each(function() {
      let type = $(this).find('a').data('type'),
        name = $(this).find('a').data('name');

      let itemSeq = 1;
      let items = [];
      stats[type] = {name:name, count:0, score:0, missScore:0};

      self.$questionForm.find('#testpaper-table-' + type).find('.js-item').each(function() {
        let itemType = $(this).data('type');
        let item = {
          id: $(this).data('id'),
          seq: itemSeq++,
        };

        let questions = [];
        if (itemType == 'material') {
          $(this).nextUntil('.js-item').each(function () {
            let question = self.getItemQuestion(this);
            questions.push(question);
            stats[type]['count'] ++;
            stats[type]['score'] += question.score;
          });
        } else {
          let question = self.getItemQuestion(this);
          questions.push(question);
          stats[type]['score'] += question.score;
          stats[type]['count'] ++;
        }

        item['questions'] = questions;
        items.push(item);
      });

      if (items.length > 0) {
        self.sections.push({name: name, seq:seq++, items:items});
      }
    });

    let total = {name:Translator.trans('activity.testpaper_manage.question_total_score'), count:0, score:0};
    $.each(stats, function(index, statsItem) {
      total.count += statsItem.count;
      total.score += statsItem.score;
    });

    stats.total = total;
    self.questionsCount = total.count;

    return stats;
  }

  getItemQuestion(that) {
    let question = {
      id: $(that).data('questionId'),
      score: Number($(that).find('.js-score').val()),
    };
    if($(that).data('type') == 'fill' && $(that).find('.js-score-type').val() == 'option'){
      question.score = question.score * $(that).data('questionAnswer').length;
    }
    if($(that).data('type') == 'material'&& $(that).data('questionType') == 'text' && $(that).find('.js-score-type').val() == 'option'){
      question.score = question.score * $(that).data('questionAnswer').length;
    }
    if($(that).find('.js-score-type').length){
      question.scoreType = $(that).find('.js-score-type').val();
      if($(that).data('type') == 'fill' || $(that).data('questionType') == 'text'){
        question.otherScore = Number($(that).find('.js-score').val());
      }else{
        question.otherScore = Number($(that).find('.js-miss-choice-score').val());
      }
    }
    if(($(that).data('type') === 'choice' || $(that).data('type') ==='uncertain_choice') && $(that).find('.js-score-type').val() == 'question'){
      question.missScore = question.otherScore;
    }
    console.log(question);
    return question;
  }

  _changeNav(event) {
    let $target = $(event.currentTarget);
    let type = $target.children().data('type');
    this.currentType = type;

    this.$typeNav.find('li').removeClass('active');
    $target.addClass('active');

    this.$form.find('.js-question-table').addClass('hide');
    this.$form.find('#testpaper-table-'+type).removeClass('hide');
    this.$form.find('[data-role="batch-select"]').prop('checked',false);
    this.$form.find('[data-role="batch-item"]').prop('checked',false);
  }

  deleteQuestion(event) {
    event.stopPropagation();
    let $target = $(event.currentTarget);
    let id = $target.closest('tr').data('id');
    let $tbody =  $target.closest('tbody');
    $tbody.find('[data-id="'+id+'"]').remove();
    $target.closest('tr').remove();
    $tbody.trigger('lengthChange');
    this.refreshSeqs($tbody.data('type'));
  }

  batchDelete(event) {
    let $target = $(event.currentTarget);
    let $tbody =  $target.parents('.js-question-table').find('tbody');
    let self = this;

    this.$form.find('[data-role="batch-item"]:checked').each(function() {
      let questionId = $(this).val();
      if ($(this).closest('tr').data('type') === 'material') {
        self.$form.find('[data-parent-id="'+questionId+'"]').remove();
      }

      $(this).closest('tr').remove();
    });
    $tbody.trigger('lengthChange');
  }

  showScoreModal(event) {
    $('.js-score-modal').find('.score-item').addClass('hidden');
    $('.js-score-modal').find('.js-score-item-num').html(0);
    let $checked = this.$form.find('[data-role="batch-item"]:checked');
    if ($checked.length > 0) {
      let self = this;
      let type = $(event.currentTarget).data('type');
      $('.js-score-modal').find('.js-tab-type').val(type);
      if( type !== 'material'){
        $('.js-score-modal').find('.js-score-set-'+type).removeClass('hidden');
        $('.js-score-modal').find('.js-score-set-'+type).find('.js-score-item-num').html($checked.length);
      }else{
        let arr = {'single_choice':'single_choice', 'choice':'choice', 'uncertain_choice':'uncertain_choice', 'true_false': 'determine', 'text':'fill', 'rich_text': 'essay'};
        for (const key in arr) {
          let count = this.$form.find(`.js-material-checkbox-${key}:checked`).length;
          $('.js-score-modal').find('.js-score-set-'+arr[key]).find('.js-score-item-num').html(count);
        }

        $('.js-score-modal').find('.score-item').removeClass('hidden');
      }

      this.$scoreModal.modal('show');
    }
  }

  batchSetScore(event) {
    let self = this;
    if (this.scoreValidator.form()) {
      let type = $('.js-score-modal').find('.js-tab-type').val();
      switch (type){
      case 'single_choice':
        self.__setJsScore(type);
        break;
      case 'choice':
        self.__setJsScore(type);
        self.__setSelectJsScore(type);
        break;
      case 'uncertain_choice':
        self.__setJsScore(type);
        self.__setSelectJsScore(type);
        break;
      case 'determine':
        self.__setJsScore(type);
        break;
      case 'fill':
        self.__setJsScore(type);
        self.__setSelectJsScore(type);
        break;
      case 'essay':
        self.__setJsScore(type);
        break;
      default:
        self.__setMaterialScore(type);
        break;
      }
      cd.message({ type: 'success', message: Translator.trans('subject.score_update_success') });
      this.$scoreModal.modal('hide');
    }
    return false;
  }

  __setMaterialScore(){
    let self =this;
    let parent = $('#testpaper-table-material');
    let target = null;
    $('.js-score-modal').find('.score-item').each(function(index,item) {
      let type = $(this).data('type');
      switch (type){
      case 'single_choice':
        target = parent.find('.js-material-single_choice');
        self.__setJsScore('single_choice', target);
        break;
      case 'choice':
        target = parent.find('.js-material-choice');
        self.__setJsScore('choice', target);
        target = parent.find('.js-material-miss-choice');
        self.__setSelectJsScore('choice', target);
        break;
      case 'uncertain_choice':
        target = parent.find('.js-material-uncertain_choice');
        self.__setJsScore('uncertain_choice', target);
        target = parent.find('.js-material-miss-uncertain_choice');
        self.__setSelectJsScore('uncertain_choice', target);
        break;
      case 'determine':
        target = parent.find('.js-material-true_false');
        self.__setJsScore('determine', target);
        break;
      case 'fill':
        target = parent.find('.js-material-text');
        self.__setJsScore('fill', target);
        self.__setSelectJsScore('fill', target);
        break;
      default:
        target = parent.find('.js-material-rich_text');
        self.__setJsScore('essay', target);
        break;
      }
    });

  }

  __setSelectJsScore(type, target = null){
    let $target = target ? target :$('#testpaper-table-'+type);
    let miss_score = $('.js-score-modal').find('.js-score-set-'+type).find('.js-miss-choice-score').val();
    $target.find('.js-miss-choice-score').val(miss_score);
    let select = $('.js-score-modal').find('.js-score-set-'+type).find('.js-score-type').val();
    $target.find('.js-score-type').val(select);
  }

  __setJsScore(type, target = null){
    let $target = target ? target :$('#testpaper-table-'+type);
    let score = $('.js-score-modal').find('.js-score-set-'+type).find('.js-score').val();
    $target.find('.js-score').val(score);
  }

  setScore($item, scoreObj) {
    let $scoreItem = $item.find('.js-question-score');
    $scoreItem.text(scoreObj.score);
    $scoreItem.attr('data-score', scoreObj.score);
    if ($item.find('.js-miss-score').length > 0) {
      let $missScoreItem = $item.find('.js-miss-score');
      $missScoreItem.text(scoreObj.missScore);
      $missScoreItem.attr('data-miss-score', scoreObj.missScore);
    }
  }

  refreshSeqs(type) {
    let seq = 1;
    let $table = this.$form.find('#testpaper-table-' + type);
    $table.find('tbody tr').each(function(index,item) {
      let $tr = $(item);

      if (!$tr.hasClass('is-sub-question')) {
        $tr.find('td.seq').html(seq);
        seq ++;
      }
    });
    $table.find('[name="questionLength"]').val((seq - 1) > 0 ? (seq - 1) : null );
  }

  changeQuestionCount(event) {
    let $target = $(event.currentTarget);
    let type = $target.data('type');
    let count = 0;
    if (type === 'material') {
      count = $target.find('tr.is-sub-question').length;
    } else {
      count = $target.find('tr').length;
    }
    $('.js-count-' + type).html('(' + count + ')');
  }

  showPickModal (event) {
    let excludeIds = [];
    let $target = $(event.currentTarget);
    this.$form.find('[name="itemIds[]"]').each(function(){
      excludeIds.push($(this).val());
    });

    let $modal = $('#modal').modal();
    $.get($target.data('url'), {exclude_ids: excludeIds.join(',')}, function(html) {
      $modal.html(html);
    });
  }

  selectQuestion(event, typeQuestions) {
    let url = this.$form.find('.js-pick-modal').data('pickUrl');
    let self = this;
    $.post(url, {typeQuestions: typeQuestions}, typeHtml=> {
      if (typeHtml) {
        $.each(typeHtml, function (type, html) {
          let $tbody = self.$questionForm.find('#testpaper-table-' + type).find('.testpaper-table-tbody');
          $tbody.append(html);
          $tbody.trigger('lengthChange');
          self.refreshSeqs(type);
          self.initTestpaperScore();
        });
      }
    });
  }

  _initEditor(validator) {
    if (this.$description.length > 0) {
      let editor = CKEDITOR.replace(this.$description.attr('id'), {
        toolbar: 'Simple',
        fileSingleSizeLimit: app.fileSingleSizeLimit,
        filebrowserImageUploadUrl: this.$description.data('imageUploadUrl'),
        height: 100
      });
      editor.on('change', () => {
        this.$description.val(editor.getData());
      });
      editor.on('blur', () => {
        this.$description.val(editor.getData());
        validator.form();
      });
    }
  }

  _initValidate() {
    this.validator = this.$form.validate({
      rules: {
        name: {
          required: true,
          maxlength: 50,
          trim: true,
        },
        description: {
          maxlength: 500,
          trim: true,
        },
        scores: {
          scoreValidate: true
        }
      },
      messages: {
        name: {
          required: Translator.trans('activity.testpaper_manage.input_title_hint'),
          maxlength: Translator.trans('site.maxlength_hint',{length: 50})
        },
        description: {
          required: Translator.trans('activity.testpaper_manage.input_description_hint'),
          maxlength: Translator.trans('site.maxlength_hint',{length: 500})
        },
      }
    });
    let self = this;
    $.validator.addMethod('scoreValidate', function (value, element) {
      $('#testpaper-items-manager').find('.jq-validate-error').remove();
      ($('#testpaper-items-manager').find('.score-validate')).each(function (event) {
        self._validateQuestionScore($(this));
      });

      return $('#testpaper-items-manager').find('.jq-validate-error').length === 0;

    }, $.validator.format(Translator.trans('testpaper.scoer.validator')));

    this._initEditor(this.validator);
  }

  _initScoreValidator() {
    this.scoreValidator = $('#batch-set-score-form').validate({
      onkeyup: false,
      rules: {
        scores: {
          scoreSetValidate:true,
        },
      },
      messages: {
      }
    });
    let self =this;
    $.validator.addMethod( 'scoreSetValidate', function(value, element, param) {
      $('#batch-set-score-form').find('.jq-validate-error').remove();

      ($('#batch-set-score-form').find('.score-validate')).each(function (event) {
        let $parent = $(this).parents('.js-question-item');
        if($parent.hasClass('hidden')){
          return;
        }
        if (!/^(([1-9]{1}\d{0,2})|([0]{1}))(\.(\d){1})?$/.test($(this).val())) {
          self._appendError($(this), Translator.trans('validate.valid_score_input.message'));
          return;
        }else{
          self._removeError($(this));
        }
        if($(this).hasClass('js-miss-choice-score')) {
          let type = $parent.find('.js-score-type').val();
          if($(this).val() > $parent.find('.js-score').val()){
            if(type === 'question'){
              self._appendError($(this), Translator.trans('course.miss_score.validator'));
            }
            if(type === 'option' ){
              self._appendError($(this), Translator.trans('testpaper.option_score.validator'));
            }
          }
        }
      });

      return $('#batch-set-score-form').find('.jq-validate-error').length === 0;
    }, '' );
  }

  _validateQuestionScore($target){
    if (!/^(([1-9]{1}\d{0,2})|([0]{1}))(\.(\d){1})?$/.test($target.val())) {
      this._appendError($target, Translator.trans('validate.valid_score_input.message'));
      return false;
    }else{
      this._removeError($target);
    }

    let $parent = $target.parents('.js-question-tr');
    if($parent.data('questionType') === 'choice' || $parent.data('questionType') === 'uncertain_choice'){
      let $answer = $parent.data('questionAnswer');
      let type = $parent.find('.js-score-type').val();

      let value = $parent.find('.js-score').val();
      let missValue = $parent.find('.js-miss-choice-score').val();

      if(type === 'question' && (missValue > value)){
        this._appendError($target, Translator.trans('course.miss_score.validator'));
        return false;
      }

      if(type === 'option' && (missValue * $answer.length > value)){
        this._appendError($target, Translator.trans('course.option_score.validator'));
        return false;
      }
    }
  }

  _appendError($event, message){
    if($event.parents('.js-question-item').find('.jq-validate-error').length ==0){
      $event.parents('.js-question-item').append(`<p class="form-error-message jq-validate-error">${message}</p>`);
    }
  }
  _removeError($event){
    $event.parents('.js-question-item').find('.jq-validate-error').remove();
  }

  _submitSave(event) {
    let $target = $(event.currentTarget);

    if(this.questionsCount > 2000){
      cd.message({ type: 'danger', message: Translator.trans('activity.testpaper_manage.questions_length_hint') });
      return;
    }

    $target.button('loading').addClass('disabled');

    let baseInfo = {
      name: this.$form.find('#name-field').val(),
      description: this.$form.find('#description-field').val(),
    };
    let sections = JSON.stringify(this.sections);

    $.post(this.$form.data('url'),{baseInfo: baseInfo, sections: sections},function(result) {
      if (result.goto) {
        window.location.href = result.goto;
      }
    });
  }

  _initTypeSort() {
    let adjustment;
    $('#testpaper-question-nav').sortable({
      handle: '.js-move-icon',
      itemSelector : '.question-type-table',
      placeholder: '<li class="question-type-table question-type-placehoder"></li>',
      onDrop: function ($item, container, _super, event) {
        $item.removeClass('dragged').removeAttr('style');
        $('body').removeClass('dragging');
      },
      onDragStart: function(item, container, _super) {
        let offset = item.offset(),
          pointer = container.rootGroup.pointer;
        adjustment = {
          left: pointer.left - offset.left,
          top: pointer.top - offset.top
        };
        _super(item, container);
      },
      onDrag: function(item, position) {
        const height = item.height();
        const width = item.width();
        item.css({
          left: position.left - adjustment.left,
          top: position.top - adjustment.top
        });
        $('.question-type-placehoder').css({
          'height': height,
          'width': width,
        });
      },
    });
  }

  initSortList() {
    let adjustment;
    const $tbody = this.$form.find('tbody');
    const td = $tbody.hasClass('js-homework-table') ? '': '<td></td>';
    const tdHtml = `<tr class="question-placehoder js-placehoder"><td></td><td></td><td></td><td></td><td></td><td></td><td></td>${td}</tr>`;
    $tbody.sortable({
      containerPath: '> tr',
      containerSelector:'tbody',
      itemSelector: 'tr.is-question',
      placeholder: tdHtml,
      exclude: '.notMoveHandle',
      onDragStart: function(item, container, _super) {
        if (!item.hasClass('have-sub-questions')) {
          $('.js-have-sub').removeClass('is-question');
        }
        let offset = item.offset(),
          pointer = container.rootGroup.pointer;
        adjustment = {
          left: pointer.left - offset.left,
          top: pointer.top - offset.top
        };
        _super(item, container);
      },
      onDrag: function(item, position) {
        const height = item.height();
        item.css({
          left: position.left - adjustment.left,
          top: position.top - adjustment.top
        });

        $('.js-placehoder').css({
          'height': height,
        });
      },
      onDrop: (item, container, _super) => {
        _super(item, container);
        if (item.hasClass('have-sub-questions')) {
          let $tbody = item.parents('tbody');
          $tbody.find('tr.is-question').each(function() {
            let $tr = $(this);
            $tbody.find('[data-id=' + $tr.data('id') + '].is-sub-question').detach().insertAfter($tr);
          });
        } else {
          $('.js-have-sub').addClass('is-question');
        }
        this.refreshSeqs(item.data('type'));
      }
    });
  }
}

new TestpaperForm($('#testpaper-form'));

