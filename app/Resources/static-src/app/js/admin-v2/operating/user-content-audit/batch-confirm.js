export default class BatchConfirm {

  constructor(prop) {
    this.element = $(prop.element);
    this.dataRole = prop.dataRole;
    this.batchConfirm();
  }

  batchConfirm () {

    const $that = $(this.element);
    const $dataRole = this.dataRole;

    this.element.on('click', '[data-role=batch-' + $dataRole + ']', function () {
      $('#modal').html('');
      let $btn = $(this),
        name = $btn.data('name'),
        ids = [],
        status = $btn.data('status');

      $that.find('[data-role=batch-item]:checked').each(function(){
        ids.push(this.value);
      });

      if (ids.length === 0) {
        cd.message({ type: 'danger', message: Translator.trans('admin_v2.operation.audit_center.batch_operate_tips',{name:name}) });
        return ;
      }

      $.get($btn.data('url'), {ids:JSON.stringify(ids),status:status}, function(res){
        $('#modal').modal('show').html(res);
      });
    });

  }
}
