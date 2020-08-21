import notify from 'common/notify';

class Module{
  constructor() {
    this.$form = $('#module-form');
    this.validate = this.initValidate();
    this.init();
  }

  init() {
    $('.js-submit-btn').on('click', event => this.submit(event));
    $('.js-delete-module').on('click', event => this.deleteModule(event));
  }

  submit(event) {
    if (this.validate.form()) {
      $.post(this.$form.attr('action'), this.$form.serialize(), function(response) {
        window.location.reload();
      }).error(function (response) {
        notify('danger', response.error.message);
      });
    }
  }

  deleteModule(event) {
    let self = this;
    let $this = $(event.currentTarget);
    $.get($this.data('checkUrl'), function (data) {
      if (data['moduleCount'] == 1) {
        notify('danger', Translator.trans('item_bank_exercise.assessment_module.module_delete.least_module_count_hint'));
        return;
      }

      if (data['assessmentCount'] > 0) {
        cd.confirm({
          title: Translator.trans('item_bank_exercise.assessment_module.module_delete.title'),
          content: Translator.trans('item_bank_exercise.assessment_module.module_delete.has_assessment_hint'),
          okText: Translator.trans('site.confirm'),
          cancelText: Translator.trans('site.close'),
        }).on('ok', () => {
          self.submitDeleteModule($this);
        });
      } else {
        cd.confirm({
          title: Translator.trans('item_bank_exercise.assessment_module.module_delete.title'),
          content: Translator.trans('item_bank_exercise.assessment_module.module_delete'),
          okText: Translator.trans('site.confirm'),
          cancelText: Translator.trans('site.close'),
        }).on('ok', () => {
          self.submitDeleteModule($this);
        });
      }
    });
  }

  submitDeleteModule($target) {
    $.post($target.data('url'), function(response) {
      window.location.reload();
    });
  }

  initValidate() {
    return this.$form.validate({
      rules: {
        title: {
          required: true,
          maxlength: 6,
          chinese_alphanumeric: true,
        }
      }
    });
  }
}

new Module();