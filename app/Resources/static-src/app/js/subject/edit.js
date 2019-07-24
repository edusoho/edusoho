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
  static getEditor(type, $form, object) {
    switch (type) {
      case 'single_choice':
        questionEditor = new SingleChoice($form, object);
        break;
      case 'uncertain_choice':
        questionEditor = new UncertainChoice($form, object);
        break;
      case 'choice':
        questionEditor = new Choice($form, object);
        break;
      case 'determine':
        questionEditor = new Determine($form, object);
        break;
      case 'essay':
        questionEditor = new Essay($form, object);
        break;
      case 'fill':
        questionEditor = new Fill($form, object);
        break;
      case 'material':
        questionEditor = new Material($form, object);
        break;
      default:
        questionEditor = new BaseQuestion($form, object);
        questionEditor.initTitleEditor();
        //questionEditor.initAnalysisEditor();
    }
  }
}

export default showEditor;