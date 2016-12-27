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
    $.get($target.data('url'), html=> {
      if (replace) {
        this.$questionAppendForm.find('tr[data-id="'+replace+'"]').replaceWith(html);
      } else {
        this.$questionAppendForm.find('tbody:visible').append(html).removeClass('hide');
      }
      this._refreshSeqs();
      this._refreshPassedDivShow();
      this.$questionPickerModal.modal('hide');
    });
  }

  questionPreview(event) {
    window.open($(event.currentTarget).data('url'), '_blank',"directories=0,height=580,width=820,scrollbars=1,toolbar=0,status=0,menubar=0,location=0");
  }

  batchSelectSave() {
    console.log('批量添加');
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

  _refreshPassedDivShow() {
    let hasEssay = false;
    this.$questionAppendForm.find('tbody tr').each(function() {
      if ($(this).data('type') == 'essay' || $(this).data('type') == 'material') {
        hasEssay = true;
      }
    });

    if (hasEssay) {
      $(".correctPercentDiv").html('');
    } else {
      var html = '这是一份纯客观题的作业，正确率达到为' +
        '<input type="text" name="passedCondition[]" class="form-control width-input width-input-mini correctPercent1" value="60" />％合格，'+
        '<input type="text" name="passedCondition[]" class="form-control width-input width-input-mini correctPercent2" value="80" />％良好，'+
        '<input type="text" name="passedCondition[]" class="form-control width-input width-input-mini correctPercent3" value="100" />％优秀';

      $(".correctPercentDiv").html(html);
    }
  }
}