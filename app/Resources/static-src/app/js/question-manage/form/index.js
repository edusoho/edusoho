import QuestionFormBase from '../type/form-base';
import Choice from '../type/choice';
import SingleChoice from '../type/single-choice';
import UncertainChoice from '../type/uncertain-choice';
import Determine from '../type/determine';
import Fill from '../type/fill';
import Essay from '../type/essay';
import Material from '../type/material';
import Vue from 'vue';
import itemBank from 'item-bank-test';

Vue.use(itemBank);

Vue.config.productionTip = false;

let questionCreator;

class QuestionCreator {
  constructor() {
  }

  static getCreator(type, $form) {
    switch (type) {
      case 'single_choice':
        questionCreator = SingleChoice;
        break;
      case 'uncertain_choice':
        questionCreator = UncertainChoice;
        break;
      case 'choice':
        questionCreator = Choice;
        break;
      case 'determine':
        questionCreator = Choice;
        break;
      case 'essay':
        questionCreator = Essay;
        break;
      case 'fill':
        questionCreator = Fill;
        break;
      case 'material':
        questionCreator = Choice;
        break;
      default:
        questionCreator = new QuestionFormBase($form);
        questionCreator.initTitleEditor();
        questionCreator.initAnalysisEditor();
    }

    return questionCreator;
  }
}
let type = $('[name="type"]').val();

new Vue({
  render: createElement => createElement(QuestionCreator.getCreator(type))
}).$mount('#app');

// let $form = $('[data-role="question-form"]');

// QuestionCreator.getCreator(type);
