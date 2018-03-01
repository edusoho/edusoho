import QuestionFormBase from '../type/form-base';
import Choice from '../type/question-choice';
import SingleChoice from '../type/question-single-choice';
import UncertainChoice from '../type/question-uncertain-choice';
import Determine from '../type/question-determine';
import Fill from '../type/question-fill';
import Essay from '../type/question-essay';
import Material from '../type/question-material';
import SelectLinkage from '../widget/select-linkage.js';


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

new SelectLinkage($('[data-role="courseId"]'),$('[data-role="lessonId"]'));
