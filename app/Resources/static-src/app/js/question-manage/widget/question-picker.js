import Emitter from 'common/es-event-emitter';

class QuestionPicker {
  constructor($pickerEle, $pickedForm) {
    this.$pickBody = $pickerEle;
    this.$modal = this.$pickBody.closest('.modal');
    this.$form = $pickedForm;
    this._initEvent();
  }

  _initEvent() { 
    this.$pickBody.find('.search-question-btn').on('click', event=>this._searchQuestion(event));
    this.$pickBody.find('[data-role="picked-item"]').on('click', event=>this._pickItem(event));
    this.$pickBody.find('.question-preview').on('click', event=>this._questionPreview(event));
  }

  _searchQuestion(event) {
    let $this = $(event.currentTarget);
    let $form = $this.closest('form');
    event.preventDefault();
    $.get($form.attr('action'), $form.serialize(), function(html) {
      $this.closest('.modal').html(html);
    });
  }

  _pickItem(event) {
    let $this = $(event.currentTarget);
    let replace = parseInt($this.data('replace'));
    let self = this;

    console.log(self.$form);

    $.get($this.data('url'), function(html) {
      
      if (replace) {
        $("#question-item-" + replace).parents('tbody').find('[data-parent-id=' + replace + ']').remove();
        $("#question-item-" + replace).replaceWith(html);
      } else {
        self.$form.find('tbody').append(html);
      }
      self._refreshSeqs();
      self._refreshPassedDivShow();

      self.$modal.modal('hide');
      self.emitter.trigger('question_picked');
    });
  }

  _refreshSeqs() {
    let seq = 1;
    this.$form.find('tbody tr').each(function(index,item) {
      let $tr = $(item);
      $tr.find('td.seq').html(seq);
      seq ++;
    });
  }

  _refreshPassedDivShow() {
    let hasEssay = false;
    this.$form.find('tbody tr').each(function() {
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

  _questionPreview(event) {
    window.open($(event.currentTarget).data('url'), '_blank',"directories=0,height=580,width=820,scrollbars=1,toolbar=0,status=0,menubar=0,location=0");
  }
}

export default QuestionPicker;