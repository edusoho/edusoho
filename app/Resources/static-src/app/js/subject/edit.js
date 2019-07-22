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
  static getEditor(type, $form) {
    switch (type) {
      case 'single_choice':
        questionEditor = new SingleChoice($form);
        break;
      case 'uncertain_choice':
        questionEditor = new UncertainChoice($form);
        break;
      case 'choice':
        questionEditor = new Choice($form);
        break;
      case 'determine':
        questionEditor = new Determine($form);
        break;
      case 'essay':
        questionEditor = new Essay($form);
        break;
      case 'fill':
        questionEditor = new Fill($form);
        break;
      case 'material':
        questionEditor = new Material($form);
        break;
      default:
        questionEditor = new BaseQuestion($form);
        questionEditor.initTitleEditor();
        //questionEditor.initAnalysisEditor();
    }
  }
}

export default showEditor;