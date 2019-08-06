import BaseQuestion from './base-question';

class Material extends BaseQuestion {
  constructor($form, object) {
    super($form, object);
    
    this.initTitleEditor(this.validator);
  }
}

export default Material;