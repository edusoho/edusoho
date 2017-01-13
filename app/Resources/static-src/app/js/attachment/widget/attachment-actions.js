class AttachmentActions {
  constructor($ele) {
    this.$ele = $ele;
    this.initEvent();
  }

  initEvent() {
    this.$ele.on('click','[data-role="delte-item"]',event=>this._deleteItem(event));
  }

  _deleteItem(event) {
    let $target = $(event.currentTarget);

    $.post($target.data('url'),{},function(response){
      if (response.msg == 'ok') {
        $target.closest('.js-attachment-list').siblings('.js-upload-file').show();
        $target.closest('.js-attachment-list').closest('div').siblings('[data-role="fileId"]').val('');
        console.log($target.closest('.js-attachment-list').closest('div').siblings('[data-role="fileId"]'));
        $target.closest('div').remove();
        $('.js-upload-file').show();
      }
    })
    
  }
}

export default AttachmentActions;