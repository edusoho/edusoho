import Choice from './choice-question';

class UncertainChoice extends Choice {
  constructor($form, object) {
    super($form, object);
    this.errorMessage = {
      noAnswer: Translator.trans('请选择正确答案'),
    };
  }

  initValidator() {
    super.initValidator();
    $.validator.addMethod('multi', function(value, element, param) {
      return true;
    });
  }
}

export default UncertainChoice;