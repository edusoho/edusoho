import ChoiceQuesiton from '../../question/type/choice-question';
import DetermineQuestion from '../../question/type/determine-question';
import EssayQuestion from '../../question/type/essay-question';
import FillQuestion from '../../question/type/fill-question';
import SingleChoiceQuestion from '../../question/type/single-choice-question';
import UncertainChoiceQuesiton from '../../question/type/single-choice-question';


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

    return questionBuilder;
  }
}

export default QuestionTypeBuilder;