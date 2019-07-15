import notify from 'common/notify';
import { questionSubjectiveRemask } from '../question-subjective';

export default class QuestionOperate {
  constructor($form, $modal) {
    this.$form = $form;
    this.$modal = $modal;
    this.initEvent();
  }

  initEvent() { 
    this.$form.on('click','[data-role="item-delete-btn"]',event=>this.deleteQuestion(event));
    this.$form.on('click','[data-role="replace-item"]',event=>this.replaceQuestion(event));
    this.$form.on('click','[data-role="preview-btn"]',event=>this.previewQuestion(event));
    this.$form.on('click','[data-role="batch-delete-btn"]',event=>this.batchDelete(event));
    this.initSortList();
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
            $tbody.find('[data-parent-id=' + $tr.data('id') + ']').detach().insertAfter($tr);
          });
        } else {
          $('.js-have-sub').addClass('is-question');
        }
        this.refreshSeqs();
      }
    });
  }

  replaceQuestion(event) {
    let $target = $(event.currentTarget);
    let excludeIds = [];
    let $tbody = this.$form.find('tbody:visible');

    $tbody.find('[name="questionIds[]"]').each(function(){
      excludeIds.push($(this).val());
    });

    this.$modal.data('manager', this).modal();
    $.get($target.data('url'), {excludeIds: excludeIds.join(','), type: $tbody.data('type')}, html => {
      this.$modal.html(html);
    });
  }

  deleteQuestion(event) {
    event.stopPropagation();
    let $target = $(event.currentTarget);
    let id = $target.closest('tr').data('id');
    let $tbody =  $target.closest('tbody');
    $tbody.find('[data-parent-id="'+id+'"]').remove();
    $target.closest('tr').remove();
    questionSubjectiveRemask(this.$form);
    $tbody.trigger('lengthChange');
    this.refreshSeqs();
  }

  batchDelete(event) {
    if (this.$form.find('[data-role="batch-item"]:checked').length == 0) {
      let $redmine = this.$form.find('.js-help-redmine');
      if($redmine) {
        $redmine.text(Translator.trans('activity.testpaper_manage.question_required_error_hint')).show();
        setTimeout(function() {
          $redmine.slideUp();
        }, 3000);
      }else {
        notify('danger', Translator.trans('activity.testpaper_manage.question_required_error_hint'));
      }
    }
    let self = this;

    this.$form.find('[data-role="batch-item"]:checked').each(function(index,item){
      let questionId = $(this).val();

      if ($(this).closest('tr').data('type') == 'material') {
        self.$form.find('[data-parent-id="'+questionId+'"]').remove();
      }
      $(this).closest('tr').remove();
      
    });
    questionSubjectiveRemask(this.$form);
  }

  previewQuestion(event) {
    event.preventDefault();
    window.open($(event.currentTarget).data('url'), '_blank', 'directories=0,height=580,width=820,scrollbars=1,toolbar=0,status=0,menubar=0,location=0');
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
}