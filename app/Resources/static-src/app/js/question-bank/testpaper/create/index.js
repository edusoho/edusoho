import { delHtmlTag } from 'common/utils';
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
  }

  _confirmSave() {
    let isOk = this._validateScore();
    let status = this.validator.form();

    if (!status || !isOk) {
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
    let questionScore = parseFloat($(that).find('.js-question-score').attr('data-score'));
    let question = {
      id: $(that).data('questionId'),
      score: questionScore
    };
    if ($(that).find('.js-miss-score').length > 0) {
      question['miss_score'] = parseFloat($(that).find('.js-miss-score').data('missScore'));
    }

    return question;
  }

  _validateScore() {
    let isOk = true;

    if (this.$form.find('.js-question-score').length === 0) {
      cd.message({type: 'danger', message: Translator.trans('activity.testpaper_manage.question_required_error_hint') });
      isOk = false;
    }

    this.$form.find('.js-question-score').each(function() {
      let itemType = $(this).closest('tr').data('type');
      let score = $(this).data('score');

      if (score == '0' && itemType !== 'material') {
        cd.message({type: 'danger', message: Translator.trans('activity.testpaper_manage.question_score_empty_hint') });
        isOk = false;
      }

      if (!/^(([1-9]{1}\d{0,2})|([0]{1}))(\.(\d){1})?$/.test(score) && itemType !== 'material') {
        cd.message({type: 'danger', message: Translator.trans('activity.testpaper_manage.question_score_error_hint') });
        isOk = false;
      }
    });

    return isOk;
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
    let $checked = this.$form.find('[data-role="batch-item"]:checked');
    if ($checked.length > 0) {
      let self = this;
      let types = ['choice', 'uncertain_choice', 'material'];
      $checked.each(function() {
        let $missScore = self.$scoreModal.find('.js-miss-score-field');
        if ($.inArray($(this).closest('tr').data('type'), types) !== -1) {
          $missScore.removeClass('hidden');
        } else {
          $missScore.addClass('hidden');
        }
      });
      this.$scoreModal.modal('show');
    }
  }

  batchSetScore(event) {
    if (this.scoreValidator.form()) {
      let $score = this.$scoreModal.find('input[name="score"]');
      let $missScore = this.$scoreModal.find('input[name="missScore"]');
      let scoreObj = {
        score: parseFloat($score.val()),
        missScore: ($missScore.val() == '') ? 0 : parseFloat($missScore.val()),
      };
      let self = this;
      this.$form.find('[data-role="batch-item"]:checked').each(function() {
        self.setScore($(this).parents('tr'), scoreObj);
      });

      cd.message({ type: 'success', message: Translator.trans('subject.score_update_success') });
      this.$scoreModal.modal('hide');
      $score.val('');
      $missScore.val('');
    }
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
        this.$description.val(delHtmlTag(editor.getData()));
      });
      editor.on('blur', () => {
        this.$description.val(delHtmlTag(editor.getData()));
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
    this._initEditor(this.validator);
  }

  _initScoreValidator() {
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

