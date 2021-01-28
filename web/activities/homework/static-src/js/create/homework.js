export default class Homework {
  constructor($iframeContent) {
    this.$homeworkModal = $('#modal', window.parent.document);
    this.$questionPickedModal = $('#attachment-modal', window.parent.document);
    this.$element = $iframeContent;
    this.$step2_form = this.$element.find('#step2-form');
    this.$step3_form = this.$element.find('#step3-form');
    this.validator2 = null;
    this.init();
  }

  init() {
    this.initEvent();
    this.setValidateRule();
    this.inItStep2form();
  }

  initEvent() {
    this.$element.on('click', '[data-role="pick-item"]', event => this.showPickQuestion(event));
    this.$questionPickedModal.on('shown.bs.modal', () => {
      this.$homeworkModal.hide();
    });
    this.$questionPickedModal.on('hidden.bs.modal', () => {
      this.showPickedQuestion();
      this.$homeworkModal.show();
      this.$questionPickedModal.html('');
      if(this.validator2) {
        this.validator2.form();
      }
    });
    this.$questionPickedModal.on('selectQuestion', (event, typeQuestions) => this.selectQuestion(event, typeQuestions));
    window.ltc.on('getActivity', (msg) => {
      window.ltc.emit('returnActivity', {valid:this.validator.form(), data:window.ltc.getFormSerializeObject($('#step2-form'))});
    });

    window.ltc.on('getValidate', (msg) => {
      window.ltc.emit('returnValidate', { valid: this.validator.form() });
    });
  }

  initCkeditor(validator) {
    const height = this.$element.data('status') ? 350 : 200;
    let editor = CKEDITOR.replace('homework-about-field', {
      toolbar: 'Task',
      fileSingleSizeLimit: app.fileSingleSizeLimit,
      filebrowserImageUploadUrl: $('#homework-about-field').data('imageUploadUrl'),
      height: height,
    });
    editor.on('change', () => {
      $('#homework-about-field').val(editor.getData());
    });
    editor.on('blur', () => {
      validator.form();
    });
  }

  showPickQuestion(event) {
    event.preventDefault();
    let $btn = $(event.currentTarget);
    let excludeIds = [];
    $('#question-table-tbody').find('[name="questionIds[]"]').each(function () {
      excludeIds.push($(this).val());
    });
    this.$questionPickedModal.modal().data('manager', this);
    $.get($btn.data('url'), {
      exclude_ids: excludeIds.join(',')
    }, html => {
      this.$questionPickedModal.html(html);
    });
  }

  showPickedQuestion() {
    let $cachedQuestion = $('.js-cached-question');
    if ($cachedQuestion.text() === '') {
      return;
    }
    let originBankId = $('.js-origin-bank').val(),
      currentBankId = $('.js-current-bank').val();
    if ($.trim(originBankId) !== $.trim(currentBankId)) {
      $('.js-homework-table').html('');
    }
    let typeQuestions = JSON.parse($cachedQuestion.text());
    $cachedQuestion.text('');
    let questionIds = [];
    $.each(Object.keys(typeQuestions), function(index, type) {
      questionIds.push.apply(questionIds, Object.keys(typeQuestions[type]));
    });
    let url = $('.js-pick-modal').data('pickUrl');
    let self = this;
    $.post(url, {itemIds: questionIds}, html => {
      let $tbody = self.$step2_form.find('tbody:visible');
      if ($tbody.length <= 0) {
        $tbody = self.$step2_form.find('tbody');
      }
      $tbody.append(html).removeClass('hide');
      $tbody.trigger('lengthChange');
      self.refreshSeq();
    });
  }

  refreshSeq() {
    let seq = 1;
    this.$step2_form.find('tbody tr').each(function(index, item) {
      let $tr = $(item);
      if (!$tr.hasClass('have-sub-questions')) {
        $tr.find('td.seq').html(seq);
        seq++;
      }
    });
    this.$step2_form.find('[name="questionLength"]').val((seq - 1) > 0 ? (seq - 1) : null );
    this.validator.form();
  }

  selectQuestion(event, typeQuestions) {
  }

  inItStep2form() {
    this.validator = this.$step2_form.validate({
      onkeyup: false,
      rules: {
        title: {
          required: true,
          maxlength: 50,
          trim: true,
          course_title: true,
        },
        description: {
          required: true
        },
        content: 'required',
        'questionLength': {
          required: true
        },
      },
      messages: {
        description: Translator.trans('activity.homework_manage.question_homework_hint'),
        questionLength: Translator.trans('activity.homework_manage.question_required_error_hint'),
      },
    });
    this.initCkeditor(this.validator);
  }

  setValidateRule() {
    $.validator.addMethod('arithmeticFloat', function (value, element) {
      return this.optional(element) || /^[0-9]+(\.[0-9]?)?$/.test(value);
    }, $.validator.format(Translator.trans('activity.homework_manage.arithmetic_float_error_hint')));

    $.validator.addMethod('positiveInteger', function (value, element) {
      return this.optional(element) || /^[1-9]\d*$/.test(value);
    }, $.validator.format(Translator.trans('activity.homework_manage.positive_integer_error_hint')));

    $.validator.addMethod('DateAndTime', function (value, element) {
      let reg = /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29) ([0-1]{1}[0-9]{1})|(2[0-4]{1}):[0-5]{1}[0-9]{1}$/;
      return this.optional(element) || reg.test(value);
    }, $.validator.format(Translator.trans('activity.homework_manage.date_and_time_error_hint:mm')));
  }
}
