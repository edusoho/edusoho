import Choice from './choice-question';

class SingleChoice extends Choice {
  constructor($form, object) {
    super($form, object);
    this.errorMessage = {
      noAnswer: Translator.trans('请选择正确答案'),
    };
  }

  initEvent() {
    super.initEvent();
    this.$form.on('change', 'input:radio[name="right"]', event => this.changeRadio(event));
  }

  initData() {
    super.initData();
    $('.cd-radio.checked').find('[name="right"]').attr('checked', true);
    this.checkedRadio = this.$form.find('.cd-radio.checked');
  }

  initValidator() {
    super.initValidator();
    $.validator.addMethod('multi', function(value, element, param) {
      return true;
    });
  }

  changeRadio(event) {
    if (this.checkedRadio) {
      this.checkedRadio.removeClass('checked');
    }
    this.checkedRadio = $(event.currentTarget).parent();
  }
}

export default SingleChoice;