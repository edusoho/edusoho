import notify from 'common/notify';

class SecurityQuestion {
  constructor(props) {
    this.element = props.element;
    this.saveBtn = props.saveBtn;
    this.$q1 = $('[name=question-1]');
    this.$q2 = $('[name=question-2]');
    this.$q3 = $('[name=question-3]');
    this.init();
  }

  init() {
    this.validator();
    this.initEvent();
  }

  initEvent() {
    const $node = $(this.element);
    const _this = this;

    $('option[value=parents]').css('display', 'none');
    $('option[value=teacher]').css('display', 'none');
    $('option[value=lover]').css('display', 'none');
    
    this.$q1.on('change', function(event) {
      let $this = $(this);
      _this.reflesh_option_display($this)
    });

    this.$q2.on('change', function(event) {
      let $this = $(this);
      _this.reflesh_option_display($this)
    });

    this.$q3.on('change', function(event) {
      let $this = $(this);
      _this.reflesh_option_display($this)
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
    })
  }

  reflesh_option_display($node) {

    if (this.$q1.val() === this.$q2.val() || this.$q3.val() === this.$q2.val() || this.$q1.val() === this.$q3.val()) {
      notify('danger',Translator.trans('user.settings.security.security_questions.type_duplicate_hint'));
      this.$q1.val('parents');
      this.$q2.val('teacher');
      this.$q3.val('lover');
      
    } else {
      $(`option[value=${$node.val()}]`).css('display', 'none');
    }

    let questions = ['parents', 'teacher', 'lover', 'schoolName', 'firstTeacher', 'hobby', 'notSelected'];

    for (let questionId in questions) {
      if (questions[questionId] !== this.$q1.val() && questions[questionId] !== this.$q2.val() && questions[questionId] !== this.$q3.val()) {
        $(`option[value=${questions[questionId]}]`).css('display', 'block');
      }
    }
  }
}

export default SecurityQuestion;