class BatchSelect {
  constructor($element) {
    this.$element = $element;
    this.initEvent();
  }

  initEvent() {
    this.$element.on('click','[data-role="batch-select"]', event=>this._batch2Item(event));
    this.$element.on('click','[data-role="batch-item"]', event=>this._item2Batch(event));
  }

  _batch2Item(event) {
    let checked = $(event.currentTarget).prop('checked');
    this.$element.find('[data-role="batch-select"]').prop('checked',checked);
    this.$element.find('[data-role="batch-item"]:visible').prop('checked',checked);
  }

  _item2Batch(event) {
    let itemLength = this.$element.find('[data-role="batch-item"]').length;
    let itemCheckedLength = this.$element.find('[data-role="batch-item"]:checked').length;

    if (itemLength == itemCheckedLength) {
      this.$element.find('[data-role="batch-select"]').prop('checked',true);
    } else {
      this.$element.find('[data-role="batch-select"]').prop('checked',false);
    }
  }
}

export default BatchSelect;