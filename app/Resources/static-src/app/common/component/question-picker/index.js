import { questionSubjectiveRemask } from '../question-subjective';

export default class QuestionPicker {
  constructor($questionPickerBody, $questionAppendForm) {
    this.$questionPickerBody = $questionPickerBody;
    this.$questionPickerModal = this.$questionPickerBody.closest('.modal');
    this.$questionAppendForm = $questionAppendForm;
    this._initEvent();
  }

  _initEvent() { 
    this.$questionPickerBody.find('[data-role="search-btn"]').on('click', event=>this.searchQuestion(event));
    this.$questionPickerBody.find('[data-role="picked-item"]').on('click', event=>this.pickItem(event));
    this.$questionPickerBody.find('[data-role="preview-btn"]').on('click', event=>this.questionPreview(event));
    this.$questionPickerBody.find('.pagination a').on('click', event=>this.pagination(event));

    let $batchSelectSave = $('[data-role="batch-select-save"]',window.parent.document);
    $batchSelectSave.on('click',event=>this.batchSelectSave(event));
  }


  pagination(event) {
    let $btn  =  $(event.currentTarget);
    $.get($btn.attr('href'),html=> {
      this.$questionPickerModal.html(html);
    });
    return false;
  }

  searchQuestion(event) {
    event.preventDefault();
    let $this = $(event.currentTarget);
    let $form = $this.closest('form');
    $.get($form.attr('action'), $form.serialize(), html => {
      this.$questionPickerModal.html(html);
    });
  }

  pickItem(event) {
    let $target = $(event.currentTarget);
    let replace = parseInt($target.data('replace'));
    let questionId = $target.data('questionId');
    let questionIds = [];
    questionIds.push(questionId);
    $target.attr('disabled',true);
    this.pickItemPost($target.data('url'),questionIds,replace);
  }

  pickItemPost(url, questionIds, replace=null) {
    
    $.post(url, {questionIds:questionIds}, html=> {
      if (replace) {
        this.$questionAppendForm.find('tr[data-id="'+replace+'"]').replaceWith(html);
        this.$questionAppendForm.find('tr[data-parent-id="'+replace+'"]').remove();
      } else {
        let $tbody = this.$questionAppendForm.find('tbody:visible');
        //fix Firefox
        if($tbody.length <= 0  ) {
          $tbody = this.$questionAppendForm.find('tbody');
        }
        $tbody.append(html).removeClass('hide');
        $tbody.trigger('lengthChange');
      }
      this._refreshSeqs();
      questionSubjectiveRemask(this.$questionAppendForm);
      this.$questionPickerModal.modal('hide');
      $('.js-close-modal').trigger('click');
    });
  }

  questionPreview(event) {
    window.open($(event.currentTarget).data('url'), '_blank','directories=0,height=580,width=820,scrollbars=1,toolbar=0,status=0,menubar=0,location=0');
  }

  batchSelectSave(event) {
    let $target = $(event.currentTarget);
    let questionIds = [];
    let url = $target.data('url');


    if (this.$questionPickerBody.find('[data-role="batch-item"]:checked').length == 0 ) {
      $('.js-choice-notice',window.parent.document).show();
      return;
    }

    this.$questionPickerBody.find('[data-role="batch-item"]:checked').each(function(index,item){
      let questionId = $(this).data('questionId');
      questionIds.push(questionId);
    });
    $target.attr('disabled',true);
    this.pickItemPost(url, questionIds,null);
  }

  _refreshSeqs() {
    let seq = 1;
    this.$questionAppendForm.find('tbody tr').each(function(index,item) {
      let $tr = $(item);
      
      if (!$tr.hasClass('have-sub-questions')) { 
        $tr.find('td.seq').html(seq);
        seq ++;
      }
    });
    this.$questionAppendForm.find('[name="questionLength"]').val((seq - 1) > 0 ? (seq - 1) : null );
  }
}