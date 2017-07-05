import notify from 'common/notify';
class AttachmentActions {
  constructor($ele) {
    this.$ele = $ele;
    this.initEvent();
  }

  initEvent() {
    this.$ele.on('click','[data-role="delte-item"]',event=>this._deleteItem(event));
  }

  _deleteItem(event) {
    let $target = $(event.currentTarget).button('loading');
    $.post($target.data('url'),{},function(response){
      if (response.msg == 'ok') {
        notify('success', Translator.trans('删除成功！'));
        $target.closest('.js-attachment-list').siblings('.js-upload-file').show();
        $target.closest('.js-attachment-list').closest('div').siblings('[data-role="fileId"]').val('');
        $target.closest('div').remove();
        $('.js-upload-file').show();
      }
    }).error(function(response){
        notify('danger', '文件不存在或正在转码，请稍后再试！');
    })
    
  }
}

export default AttachmentActions;