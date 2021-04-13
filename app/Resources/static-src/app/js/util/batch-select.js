export default class BatchSelect {
  constructor(prop) {
    this.element = $(prop.element);
    this.batchSelect();
    this.batchItem();
  }

  batchSelect() {

    const $that = $(this.element);

    this.element.on('click', '[data-role=batch-select]', function () {
      if( $(this).is(":checked") === true){
          $('.js-batch-tag-btn, .js-batch-delete-btn, .js-batch-download').attr('disabled', false);
        $that.find('[data-role=batch-select], [data-role=batch-item]').prop('checked', true);
        } else {
          $('.js-batch-tag-btn, .js-batch-delete-btn, .js-batch-download').attr('disabled', true);
        $that.find('[data-role=batch-select], [data-role=batch-item]').prop('checked', false);
        }

    });
  }

  batchItem() {

    const $that = $(this.element);

    this.element.on('click', '[data-role=batch-item]', function () {
      let length = $that.find('[data-role=batch-item]').length;
      let checked_count = 0;
      $that.find('[data-role=batch-item]').each(function(){
        if ( $(this).is(":checked")) {
          checked_count++;
        }
      })

      if (checked_count === length){
        $that.find('[data-role=batch-select]').prop('checked',true);
      } else {
        $that.find('[data-role=batch-select]').prop('checked',false);
      }
      if (checked_count !== 0) {
        $('.js-batch-tag-btn, .js-batch-delete-btn, .js-batch-download').attr('disabled', false);
      } else {
        $('.js-batch-tag-btn, .js-batch-delete-btn, .js-batch-download').attr('disabled', true);
      }

    });
  }

}