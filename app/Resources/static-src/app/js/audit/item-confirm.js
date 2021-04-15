export default class ItemConfirm {

  constructor(prop) {
    this.element = $(prop.element);
    this.dataRole = prop.dataRole;
    this.itemConfirm();
  }

  itemConfirm() {
    const $dataRole = this.dataRole;
    this.element.on('click', '[data-role=item-' + $dataRole + ']', function(onSuccess) {
      let $btn = $(this),
        name = $btn.data('name');

      $('#modal-' + $dataRole).modal('show');

      $('.cancel').click(function(){
        $('#modal-' + $dataRole).modal('hide');
      });

      $('.confirm').click(function(){
        $.post($btn.data('url'), function() {
          if ($.isFunction(onSuccess)) {
            onSuccess.call($element, $item);
          } else {
            cd.message({ type: 'success', message: Translator.trans('admin_v2.operation.user_content_audit.tip.message',{name:name}) });
            window.location.reload();
          }
        });
      });

    });
  }
}