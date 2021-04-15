export default class BatchConfirm {

  constructor(prop) {
    this.element = $(prop.element);
    this.dataRole = prop.dataRole;
    this.batchConfirm();
  }

  batchConfirm () {

    const $that = $(this.element);
    const $dataRole = this.dataRole;

    this.element.on('click', '[data-role=batch-' + $dataRole + ']', function (onSuccess) {
      let $btn = $(this);
      let name = $btn.data('name');
      let ids = [];

      let status = $dataRole === 'confirm-pass' ? 'pass' : 'illegal';

      $that.find('[data-role=batch-item]:checked').each(function(){
        ids.push(this.value);
      });

      if (ids.length === 0) {
        cd.message({ type: 'danger', message: Translator.trans('admin_v2.operation.user_content_audit.tip.checked_empty_hint',{name:name}) });
        return ;
      }


      $('#modal-' + $dataRole).modal('show');

      $('.cancel').click(function(){
        $(this.element).find('.btn').addClass('disabled');
        $('#modal-' + $dataRole).modal('hide');
      });

      $('.confirm').click(function(){
        $(this.element).find('.btn').addClass('disabled');
        $.post($btn.data('url'), {ids:ids,status:status}, function(){
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
