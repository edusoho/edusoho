import notify from 'common/notify';
export default class QuestionManage{
  constructor($element) {
    this.$element = $element;
    this.$button = this.$element.find('[data-role="pick-item"]');
    this.$typeNav = this.$element.find('#testpaper-question-nav');
    this.$modal = $('#testpaper-confirm-modal');
    this.currentType = this.$typeNav.find('.active').children().data('type');
    this.questions = [];
    this.questionsCount = 0;
    this._initEvent();
    this.initTypeSort();
  }

  _initEvent() {
    this.$button.on('click',event => this._showPickerModal(event));
    this.$typeNav.on('click','li', event => this._changeNav(event));
    this.$element.on('click','.js-request-save',event => this._confirmSave(event));
    this.$modal.on('click','.js-confirm-submit',event => this._submitSave(event));
    this.$element.on('lengthChange','[data-role="question-body"]', event => this.changeQuestionCount(event));
  }

  _showPickerModal() {
    let excludeIds = [];
    $('[data-type="'+this.currentType+'"]').find('[name="questionIds[]"]').each(function(){
      excludeIds.push($(this).val());
    });

    let $modal = $('#modal').modal();
    $modal.data('manager', this);
    $.get(this.$button.data('url'), {excludeIds: excludeIds.join(','), type: this.currentType}, function(html) {
      $modal.html(html);
    });
  }

  _changeNav(event) {
    let $target = $(event.currentTarget);
    let type = $target.children().data('type');
    this.currentType = type;

    this.$typeNav.find('li').removeClass('active');
    $target.addClass('active');

    this.$element.find('[data-role="question-body"]').addClass('hide');
    this.$element.find('#testpaper-items-'+type).removeClass('hide');
    this.$element.find('[data-role="batch-select"]').prop('checked',false);
    this.$element.find('[data-role="batch-item"]').prop('checked',false);
  }

  _confirmSave() {
    let isOk = this._validateScore();

    if (!isOk) {
      return ;
    }

    this.questionsCount = 0;
    this.questions = [];
    let stats = this._calTestpaperStats();

    let html='';
    $.each(stats, function(index, statsItem){
      let tr = '<tr>';
      tr += '<td>' + statsItem.name + '</td>';
      tr += '<td>' + statsItem.count + '</td>';
      tr += '<td>' + statsItem.score.toFixed(1) + '</td>';
      tr += '</tr>';
      html += tr;
    });

    this.$modal.find('.detail-tbody').html(html);

    this._showSubjectiveRemaskIfNoEssay();

    this.$modal.modal('show');
  }

  _validateScore() {
    let isOk = true;

    if (this.$element.find('[name="scores[]"]').length == 0) {
      notify('danger',Translator.trans('activity.testpaper_manage.question_required_error_hint'));
      isOk = false;
    }

    this.$element.find('input[type="text"][name="scores[]"]').each(function() {
      var score = $(this).val();

      if (score == '0') {
        notify('danger',Translator.trans('activity.testpaper_manage.question_score_empty_hint'));
        isOk = false;
      }

      if (!/^(([1-9]{1}\d{0,2})|([0]{1}))(\.(\d){1})?$/.test(score)) {
        notify('danger', Translator.trans('activity.testpaper_manage.question_score_error_hint'));
        $(this).focus();
        isOk = false;
      }
    });

    return isOk;
  }

  _calTestpaperStats() {
    let stats = {};
    let self = this;

    this.$typeNav.find('li').each(function() {
      let type = $(this).find('a').data('type'),
        name = $(this).find('a').data('name');
            

      stats[type] = {name:name, count:0, score:0, missScore:0};

      self.$element.find('#testpaper-items-'+type).find('[name="scores[]"]').each(function() {
        let itemType = $(this).closest('tr').data('type');
        let score = itemType == 'material' ? 0 : parseFloat($(this).val());
        let question = {};

        if (itemType != 'material') {
          stats[type]['count'] ++;
        }
            
        stats[type]['score'] += score;
        stats[type]['missScore'] = parseFloat($(this).data('miss-score'));

        let questionId = $(this).closest('tr').data('id');

        question['id'] = questionId;
        question['score'] = score;
        question['missScore'] = parseFloat($(this).data('miss-score'));
        question['type'] = type;
            
        self.questions.push(question);
      });
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

  _showSubjectiveRemaskIfNoEssay() {
    let $remask = $('.js-subjective-remask');
    let $essay = $('#testpaper-table tr[data-type="essay"]');

    if ($essay.length > 0 && !$remask.hasClass('hidden')) {
        $remask.addClass('hidden');
    }

    if ($essay.length === 0 && $remask.hasClass('hidden')) {
        $remask.removeClass('hidden');
    }
  }

  _submitSave(event) {
    let passedScore = 0;
    let $target = $(event.currentTarget);

    if ($('[name="passedScore"]').length > 0) {
      let passedScoreErrorMsg = $('.passedScoreDiv').siblings('.help-block').html();
      if ($.trim(passedScoreErrorMsg) !== '') {
        return ;
      }
    }

    this.questionsCount = 0;
    this.questions = [];
    let stats = this._calTestpaperStats();

    if ($('[name="passedScore"]:visible').length > 0) {
      passedScore = $('input[name="passedScore"]').val();
      if (passedScore > stats.total.score) {
        notify('danger', Translator.trans('activity.testpaper_manage.setting_pass_score_error_hint', {'passedScore':passedScore, 'totalScore':stats.total.score}));
        return;
      }

      if (!/^(([1-9]\d{0,2})|([0]))(\.(\d))?$/.test(passedScore)) {
        notify('danger', Translator.trans('activity.testpaper_manage.pass_score_error_hint'));
        $(this).focus();
        return;
      }
    }
    let questionTypeSeq = [];
    $("input[name='questionTypeSeq']").each(function(){
        questionTypeSeq.push($(this).val());
    })

    if (this.questionsCount > 2000) {
        notify('danger', Translator.trans('activity.testpaper_manage.questions_length_hint'));
    }else{
        $target.button('loading').addClass('disabled');
        $.post(this.$element.attr('action'),{questions: JSON.stringify(this.questions),passedScore: passedScore, questionTypeSeq: JSON.stringify(questionTypeSeq)},function(result) {
          if (result.goto) {
            window.location.href = result.goto;
          }
        });
    }

  }

  changeQuestionCount(event) {
    let $target = $(event.currentTarget);
    let type = $target.data('type');
    let count = 0;
    if (type == 'material') {
      count = $target.find('tr.is-sub-question').length;
    } else {
      count = $target.find('tr').length;
    }
    $('.js-count-' + type).html('(' + count + ')');
  }

  initTypeSort() {
    var $group = $('#testpaper-question-nav');
    var adjustment;
    $('#testpaper-question-nav').sortable({
      handle: '.js-move-icon',
      itemSelector : '.question-type-table',
      placeholder: '<li class="question-type-table question-type-placehoder"></li>', 
      onDrop: function ($item, container, _super, event) {
        $item.removeClass('dragged').removeAttr('style');
        $('body').removeClass('dragging');
      },
      onDragStart: function(item, container, _super) {
        var offset = item.offset(),
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
}