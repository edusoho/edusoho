import notify from 'common/notify';

class SecurityQuestion {
  constructor(props) {
    this.element = props.element;
    this.saveBtn = props.saveBtn;
    this.$q1 = $('[name=question-1]');
    this.$q2 = $('[name=question-2]');
    this.$q3 = $('[name=question-3]');

    this.selectOptions = [];

    this.init();
  }

  init() {
    this.validator();
    this.initEvent();
  }

  initEvent() {
    const $node = $(this.element);
    const _this = this;

    this.changeOptions();

    cd.select({
      el: '.js-cd-select',
    }).on('beforeChange', (value, text) => {
      if (this.selectOptions.includes(value)) {
        notify('danger', Translator.trans('user.settings.security.security_questions.type_duplicate_hint'));
        throw new Error(Translator.trans('user.settings.security.security_questions.type_duplicate_hint'));
      }

    }).on('change', (value, text) => {
      this.changeOptions();
    });
  }

  changeOptions() {
    this.selectOptions = [];
    [this.$q1, this.$q2, this.$q3].forEach((item) => {
      this.selectOptions.push(item.val());
    });
  }

  validator() {
    let btn = this.saveBtn;
    $(this.element).validate({
      currentDom: btn,
      ajax: true,
      rules: {
        'answer-1': {
          required: true,
          maxlength: 20
        },
        'answer-2': {
          required: true,
          maxlength: 20
        },
        'answer-3': {
          required: true,
          maxlength: 20
        },
        'userLoginPassword': 'required'
      },
      submitSuccess(data) {
        notify('success', Translator.trans(data.message));
        
        $('.modal').modal('hide');
        window.location.reload();
      },
      submitError(data) {
        notify('danger',  Translator.trans(data.responseJSON.message));
      }
    });
  }
}

export default SecurityQuestion;