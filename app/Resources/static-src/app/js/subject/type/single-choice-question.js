import Choice from './choice-question';

class SingleChoice extends Choice {
  constructor($form) {
    super($form);
    this.checkedRadio = null;
  }

  initEvent() {
    this.$form.on('focus', '.js-item-option-edit', event => this.editOption(event));
    this.$form.on('click', '.js-item-option-delete', event => this.deleteOption(event));
    this.$form.on('click', '.js-item-option-add', event => this.addOption(event));
    this.$form.on('change', 'input:radio[name="right"]', event => this.changeRadio(event));
  }

  changeRadio(event) {
    if (this.checkedRadio) {
      this.checkedRadio.removeClass('checked');
    }
    this.checkedRadio = $(event.currentTarget).parent();
  }
}

export default SingleChoice;