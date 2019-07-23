import BaseQuestion from './type/base-question';
import Choice from './type/choice-question';
import SingleChoice from './type/single-choice-question';
import UncertainChoice from './type/uncertain-choice-question';
import Determine from './type/determine-question';
import Fill from './type/fill-question';
import Essay from './type/essay-question';
import Material from './type/material-question';

let questionEditor;
class showEditor {
  static getEditor(type, $form, questionOperate) {
    switch (type) {
      case 'single_choice':
        questionEditor = new SingleChoice($form, questionOperate);
        break;
      case 'uncertain_choice':
        questionEditor = new UncertainChoice($form, questionOperate);
        break;
      case 'choice':
        questionEditor = new Choice($form, questionOperate);
        break;
      case 'determine':
        questionEditor = new Determine($form, questionOperate);
        break;
      case 'essay':
        questionEditor = new Essay($form, questionOperate);
        break;
      case 'fill':
        questionEditor = new Fill($form, questionOperate);
        break;
      case 'material':
        questionEditor = new Material($form, questionOperate);
        break;
      default:
        questionEditor = new BaseQuestion($form, questionOperate);
        questionEditor.initTitleEditor();
        //questionEditor.initAnalysisEditor();
    }
  }
}

export default showEditor;