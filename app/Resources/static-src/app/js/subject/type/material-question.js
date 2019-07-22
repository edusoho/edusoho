import BaseQuestion from './base-question';

class Material extends BaseQuestion {
  constructor($form) {
    super($form);
    
    this.initTitleEditor(this.validator);
    //this.initAnalysisEditor();
  }
}

export default Material;