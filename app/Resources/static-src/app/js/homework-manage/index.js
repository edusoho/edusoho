import Emitter from 'common/es-event-emitter';

class QuestionPicker
{
  constructor($pickerEle, $pickedForm)
  {
    this.$pickBody = $pickerEle;
    this.$modal = this.$pickBody.closest('.modal');
    this.$form = $pickedForm;
    this.emitter = new Emitter();

    this._initEvent();
  }

  _initEvent()
  { 
    this.$pickBody.find('[data-role="search-btn"]').on('click', event=>this._searchQuestion(event));
    this.$pickBody.find('[data-role="picked-item"]').on('click', event=>this._pickItem(event));
    this.$pickBody.find('[data-role="preview-btn"]').on('click', event=>this._questionPreview(event));
  }

  _searchQuestion(event)
  {
    let $this = $(event.currentTarget);
    let $form = $this.closest('form');
    event.preventDefault();

    $.get($form.attr('action'), $form.serialize(), function(html) {
        $this.closest('.modal').html(html);
    });
  }

  _pickItem(event)
  {
    let $target = $(event.currentTarget);
    let replace = parseInt($target.data('replace'));
    let self = this;

    $.get($target.data('url'), function(html) {
      if (replace) {
        self.$form.find('tr[data-id="'+replace+'"]').replaceWith(html);
      } else {
        self.$form.find('tbody:visible').append(html).removeClass('hide');
      }
      self._refreshSeqs();
      self._refreshPassedDivShow();

      self.$modal.modal('hide');

      self.emitter.trigger('question_picked');
    });
  }

  _refreshSeqs()
  {
    let seq = 1;
    this.$form.find('tbody tr').each(function(index,item) {
      let $tr = $(item);
      $tr.find('td.seq').html(seq);
      seq ++;
    });

    //$('#homework_items_help').hide();
  }

  _refreshPassedDivShow()
  {
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

  _questionPreview(event)
  {
    window.open($(event.currentTarget).data('url'), '_blank',
                "directories=0,height=580,width=820,scrollbars=1,toolbar=0,status=0,menubar=0,location=0");
  }
}

new QuestionPicker($('#question-picker-body',window.parent.document), $('#step2-form'));
