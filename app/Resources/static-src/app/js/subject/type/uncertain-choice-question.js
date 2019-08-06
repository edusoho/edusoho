import Choice from './choice-question';

class UncertainChoice extends Choice {
  constructor($form, object) {
    super($form, object);
    this.errorMessage = {
      noAnswer: Translator.trans('subject.choice_require_answer'),
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