import { passedDivShow } from '../question-passed'
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
    let $batchSelectSave = $('[data-role="batch-select-save"]',window.parent.document);
    $batchSelectSave.on('click',event=>this.batchSelectSave(event));
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
    
    this.pickItemGet($target.data('url'),questionIds,replace);
  }

  pickItemGet(url, questionIds, replace=null) {
    $.get(url, {questionIds:questionIds}, html=> {
      if (replace) {
        this.$questionAppendForm.find('tr[data-id="'+replace+'"]').replaceWith(html);
        this.$questionAppendForm.find('tr[data-parent-id="'+replace+'"]').remove();
      } else {
        this.$questionAppendForm.find('tbody:visible').append(html).removeClass('hide');
      }
      this._refreshSeqs();
      passedDivShow(this.$questionAppendForm);
      this.$questionPickerModal.modal('hide');
    });
  }

  questionPreview(event) {
    window.open($(event.currentTarget).data('url'), '_blank',"directories=0,height=580,width=820,scrollbars=1,toolbar=0,status=0,menubar=0,location=0");
  }

  batchSelectSave(event) {
    let $target = $(event.currentTarget);
    let questionIds = [];
    let url = $target.data('url');

    this.$questionPickerBody.find('[data-role="batch-item"]:checked').each(function(index,item){
      let questionId = $(this).data('questionId');
      questionIds.push(questionId);
    })

    this.pickItemGet(url, questionIds,null);
  }

  _refreshSeqs() {
    let seq = 0;
    this.$questionAppendForm.find('tbody tr').each(function(index,item) {
      let $tr = $(item);
      $tr.find('td.seq').html(seq+1);
      seq++;
    });
    this.$questionAppendForm.find('[name="questionLength"]').val(seq > 0 ? seq : null );
  }
}