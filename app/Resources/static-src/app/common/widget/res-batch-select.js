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
    if (checked) {
      $('.js-batch-tag-btn, .js-batch-delete-btn, .js-batch-share-btn, .js-batch-download').attr('disabled', false);
    } else {
      if (this.$element.find('[data-role="batch-item"]:checked').length == 0) {
        $('.js-batch-tag-btn, .js-batch-delete-btn, .js-batch-share-btn, .js-batch-download').attr('disabled', true);
      }
    }
  }

  _item2Batch(event) {
    let itemLength = this.$element.find('[data-role="batch-item"]:visible').length;
    let itemCheckedLength = this.$element.find('[data-role="batch-item"]:checked').length;
    if (itemCheckedLength !== 0) {
      $('.js-batch-tag-btn, .js-batch-delete-btn, .js-batch-share-btn, .js-batch-download').attr('disabled', false);
    } else {
      $('.js-batch-tag-btn, .js-batch-delete-btn, .js-batch-share-btn, .js-batch-download').attr('disabled', true);
    }
    if (itemLength == itemCheckedLength) {
      this.$element.find('[data-role="batch-select"]').prop('checked',true);
    } else {
      this.$element.find('[data-role="batch-select"]').prop('checked',false);
    }
  }
}

export default BatchSelect;