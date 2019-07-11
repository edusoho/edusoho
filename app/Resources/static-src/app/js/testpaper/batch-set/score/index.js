export default class BatchSet {
  constructor() {
    this.$form = null;
    this.$modal = $('#modal');
    this.questions = [];
    this.questionCounts = {};
    this.questionTypes = [''];
    this.validator = null;

    this._initEvent();
  }

  _initEvent() {
    let self = this;

    $('.js-batch-score').click(function() {
      $.ajax({
        type: 'get',
        url: $(this).data('url'),
      }).done(function(resp) {
        self.$modal.html(resp);
        $('.js-selected').text(self._selectedQuestionText());

        if (self.questionCounts['choose'] > 0 || self.questionCounts['uncertain'] > 0) {
          $('miss-score-field').removeClass('hidden');
        }

        self.$modal.modal('show');

        self.$form = $('#batch-set-score-form');
        self._initValidate();
      });
    });

    $('#modal').on('click', '.js-batch-score-confirm', function() {
      if (self.validator.form()) {
        self.$modal.modal('hide');
      }
    });
  }

  _initValidate() {
    this.validator = this.$form.validate({
      onkeyup: false,
      rules: {
        score: {
          required: true,
          // number: true,
          digits: true,
          max: 999,
          min: 0,
          es_score: true
        },
        missScore: {
          required: false,
          // number: true,
          digits: true,
          max: 999,
          min: 0,
          noMoreThan: '#score',
          es_score: true
        }
      },
      messages: {
        missScore: {
          noMoreThan: '漏选分值不得超过题目分值'
        }
      }
    });

    $.validator.addMethod( "noMoreThan", function(value, element, param) {
      return value <= $(param).val();
    }, 'Please enter a lesser value.' );
  }

  _selectedQuestionText() {
    let text = '已选:';

    // text = text + `道${this.questionTypes},`;
    return text;
  }
}

new BatchSet();