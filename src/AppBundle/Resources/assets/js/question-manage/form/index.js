import QuestionFormBase from '../../../common/question-manage/form-base';
import Choice from '../../../common/question-manage/question-choice';
import SingleChoice from '../../../common/question-manage/question-single-choice';
import UncertainChoice from '../../../common/question-manage/question-uncertain-choice';
import Determine from '../../../common/question-manage/question-determine';
import Fill from '../../../common/question-manage/question-fill';
import Essay from '../../../common/question-manage/question-essay';
import Material from '../../../common/question-manage/question-material';

class QuestionCreator {
  constructor() {

  }

  static getCreator(type, $form) {
    switch (type) {
      case 'single_choice':
        QuestionCreator = new SingleChoice($form);
        break;
      case 'uncertain_choice':
        QuestionCreator = new UncertainChoice($form);
        break;
      case 'choice':
        QuestionCreator = new Choice($form);
        break;
      case 'determine':
        QuestionCreator = new Determine($form);
        break;
      case 'essay':
        QuestionCreator = new Essay($form);
        break;
      case 'fill':
        QuestionCreator = new Fill($form);
        break;
      case 'material':
        QuestionCreator = new Material($form);
        break;
      default:
        QuestionCreator = new QuestionFormBase($form);
        QuestionCreator.initTitleEditor();
        QuestionCreator.initAnalysisEditor();
    }

    return QuestionCreator;
  }
}

let $form = $('[data-role="question-form"]');
let type = $('[data-role="question-form"]').find('[name="type"]').val();

QuestionCreator.getCreator(type, $form);
