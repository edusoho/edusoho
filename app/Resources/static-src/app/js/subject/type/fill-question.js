import BaseQuestion from './base-question';

jQuery.validator.addMethod('fillCheck', function (value, element) {
  return this.optional(element) || /(\[\[(.+?)\]\])/i.test(value);
}, Translator.trans('course.question.create.fill_hint'));

class Fill extends BaseQuestion {
  constructor($form, object) {
    super($form, object);
    this.titleEditorToolBarName = 'Question';
    this.initTitleEditor(this.validator);
    
    this.$titleField = $('#'+this.titleFieldId);
    this.init();
  }

  init() {
    this.$titleField.rules('add',{
      fillCheck:true
    });
  }

  filterQuestion(question) {
    let matches = question['stem'].match(/\[\[(.+?)\]\]/g);
    if (matches != null) {
      question['answers'] = [];
      for (var i = 0; i < matches.length; i++) {
        let answer = matches[i].substring(2);
        answer = answer.substring(0, answer.length - 2);
        question['answers'].push(answer);
      }
    }

    return question;
  }
}

export default Fill;