import ChoiceQuesiton from '../choice-question/choice-question';
import DetermineQuestion from '../determine-question/determine-question';
import EssayQuestion from '../essay-question/essay-question';
import FillQuestion from '../fill-question/fill-question';
import SingleChoiceQuestion from '../single-choice-question/single-choice-question';
import UncertainChoiceQuesiton from '../single-choice-question/single-choice-question';


class QuestionTypeBuilder
{
	constructor(type)
	{
		this.type = type;
	}

	static getTypeBuilder(type) {
		let questionBuilder = null;
		switch (type) {
			case 'choice':
				questionBuilder = new ChoiceQuesiton();
				break;
			case 'determine':
				questionBuilder = new DetermineQuestion();
				break;
			case 'essay':
				questionBuilder = new EssayQuestion();
				break;
			case 'fill':
				questionBuilder = new FillQuestion();
				break;
			case 'single_choice':
				questionBuilder = new SingleChoiceQuestion();
				break;
			case 'uncertain_choice':
				questionBuilder = new UncertainChoiceQuesiton();
				break;
			default:
				questionBuilder = null;
		}

		return questionBuilder
	}
}

export default QuestionTypeBuilder;