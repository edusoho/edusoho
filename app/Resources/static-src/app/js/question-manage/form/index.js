import QuestionFormBase from '../type/form-base';
import Choice from '../type/question-choice';
import SingleChoice from '../type/question-single-choice';
import UncertainChoice from '../type/question-uncertain-choice';
import Determine from '../type/question-determine';
import Fill from '../type/question-fill';
import Essay from '../type/question-essay';
import Material from '../type/question-material';
import SelectLinkage from '../widget/select-linkage.js';

let questionCreator;
class QuestionCreator {
	constructor() {
	}

	static getCreator(type, $form) {
		switch (type) {
		case 'single_choice':
			questionCreator = new SingleChoice($form);
			break;
		case 'uncertain_choice':
			questionCreator = new UncertainChoice($form);
			break;
		case 'choice':
			questionCreator = new Choice($form);
			break;
		case 'determine':
			questionCreator = new Determine($form);
			break;
		case 'essay':
			questionCreator = new Essay($form);
			break;
		case 'fill':
			questionCreator = new Fill($form);
			break;
		case 'material':
			questionCreator = new Material($form);
			break;
		default:
			questionCreator = new QuestionFormBase($form);
			questionCreator.initTitleEditor();
			questionCreator.initAnalysisEditor();
		}

		return questionCreator;
	}
}

let $form = $('[data-role="question-form"]');
let type = $('[data-role="question-form"]').find('[name="type"]').val();

QuestionCreator.getCreator(type, $form);

new SelectLinkage($('[data-role="courseId"]'),$('[data-role="lessonId"]'));
