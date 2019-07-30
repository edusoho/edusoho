import BaseQuestion from './base-question';

class Datermine extends BaseQuestion {
  constructor($form, object) {
    super($form, object);

    this.initTitleEditor(this.validator);

    this.$answerField = $('[name="answer"]');

    this.init();
  }

  init() {
    this.$answerField.rules('add', {
      required: true,
      messages: {
        required: Translator.trans('course.question.create.right_answer_required_error_hint')
      }
    });
  }

  filterQuestion(question) {
    let answer = $('input[name=\'answer\']:checked').val();
    question['answer'] = (answer == 'true') ? true : false;

    return question;
  }
}

export default Datermine;