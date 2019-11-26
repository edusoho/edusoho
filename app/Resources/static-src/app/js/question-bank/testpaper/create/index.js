import { delHtmlTag } from 'common/utils';
import BatchSelect from '../../../../common/widget/batch-select';

class TestpaperForm {
  constructor($form) {
    this.$form = $form;
    this.$description = this.$form.find('[name="description"]');
    this.validator = null;
    this.difficultySlider = null;
    this.scoreSlider = null;
    this.$scoreModal = $('.js-score-modal');
    this.$typeNav = this.$form.find('#testpaper-question-nav');
    new BatchSelect($('#testpaper-items-manager'));
    this._initEvent();
    this._initValidate();
    this._initScoreValidator();
  }

  _initEvent() {
    // this.$form.on('click', '[data-role="submit"]', event => this._submit(event));
    this.$typeNav.on('click', 'li', event => this._changeNav(event));
    this.$form.on('click', '[data-role="item-delete-btn"]', event=>this.deleteQuestion(event));
    this.$form.on('click', '[data-role="batch-delete-btn"]', event=>this.batchDelete(event));
    this.$form.on('click', '[data-role="set-score-btn"]', event=>this.showScoreModal(event));
    this.$form.on('lengthChange','[data-role="question-body"]', event => this.changeQuestionCount(event));
    this.$scoreModal.on('click', '.js-batch-score-confirm', event => this.batchSetScore(event));
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
    $tbody.find('[data-parent-id="'+id+'"]').remove();
    $target.closest('tr').remove();
    $tbody.trigger('lengthChange');
    this.refreshSeqs();
  }

  batchDelete(event) {
    let $target = $(event.currentTarget);
    let $tbody =  $target.parents('.js-question-table').find('tbody');
    let self = this;

    this.$form.find('[data-role="batch-item"]:checked').each(function(index,item){
      let questionId = $(this).val();

      if ($(this).closest('tr').data('type') == 'material') {
        self.$form.find('[data-parent-id="'+questionId+'"]').remove();
      }
      $(this).closest('tr').remove();

    });
    $tbody.trigger('lengthChange');
  }

  showScoreModal(event) {
    if (this.$form.find('[data-role="batch-item"]:checked').length > 0) {
      let self = this;
      var types = ['choice', 'uncertain_choice'];
      this.$form.find('[data-role="batch-item"]:checked').each(function(index,item){
        if ($.inArray($(this).closest('tr').data('type'), types) != -1) {
          self.$scoreModal.find('.js-miss-score-field').removeClass('hidden');
        }
      });
      this.$scoreModal.modal('show');
    }
  }

  batchSetScore(event) {
    if (this.scoreValidator.form()) {
      let self = this;
      let score = parseFloat(this.$scoreModal.find('input[name="score"]').val());
      let missScore = parseFloat(this.$scoreModal.find('input[name="missScore"]').val());
      let scoreObj = {
        score: score,
        missScore: missScore,
      };
      this.$form.find('[data-role="batch-item"]:checked').each(function() {
        let questionId = $(this).val();

        if ($(this).closest('tr').data('type') == 'material') {
          self.$form.find('[data-parent-id="'+questionId+'"]').each(function () {
            self.setScore($(this), scoreObj);
          });
        } else {
          self.setScore($(this).parents('tr'), scoreObj);
        }
      });

      cd.message({ type: 'success', message: Translator.trans('subject.score_update_success') });
      this.$scoreModal.modal('hide');
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

  refreshSeqs() {
    let seq = 1;
    this.$form.find('tbody tr').each(function(){
      let $tr = $(this);

      if (!$tr.hasClass('have-sub-questions')) {
        $tr.find('td.seq').html(seq);
        seq ++;
      }
    });

    this.$form.find('[name="questionLength"]').val((seq - 1) > 0 ? (seq - 1 ) : null );
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

  _initEditor(validator) {
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

  _initValidate() {
    this.validator = this.$form.validate({
      rules: {
        name: {
          required: true,
          maxlength: 50,
          trim: true,
        },
        description: {
          //required: true,
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

  // _submit(event) {
  //   let $target = $(event.currentTarget);
  //   let status = this.validator.form();
  //   let questionNum = 0;
  //   this.$form.find('[data-role="count"]').each(function () {
  //     let self = $(this);
  //     questionNum+=Number(self.val());
  //   });
  //
  //   if (status) {
  //     if(questionNum>2000){
  //       notify('danger', Translator.trans('activity.testpaper_manage.questions_length_hint'));
  //     }else{
  //       $.post($target.data('checkUrl'),this.$form.serialize(),result => {
  //         if (result.status == 'no') {
  //           $('.js-build-check').html(Translator.trans('activity.testpaper_manage.question_num_error'));
  //         } else {
  //           $('.js-build-check').html('');
  //
  //           $target.button('loading').addClass('disabled');
  //           this.$form.submit();
  //         }
  //       });
  //     }
  //   }
  // }
}

new TestpaperForm($('#testpaper-form'));

