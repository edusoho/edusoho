export default class BatchDelete {

  constructor(prop) {
    this.element = $(prop.element);
    this.batchDelete();
  }

  batchDelete () {

    const $that = $(this.element);

    this.element.on('click', '[data-role=batch-delete]', function () {

      let $btn = $(this);
      let name = $btn.data('name');
      let ids = [];

      $that.find('[data-role=batch-item]:checked').each(function(){
        ids.push(this.value);
      });

      if (ids.length === 0) {
        cd.message({ type: 'danger', message: Translator.trans('admin.util.batch_delete.checked_empty_hint',{name:name}) });
        return ;
      }

      if (!confirm(Translator.trans('admin.util.batch_delete.delete_hint',{ids:ids.length,name:name}))) {
        return ;
      }

      $(this.element).find('.btn').addClass('disabled');

      cd.message({ type: 'info', message: Translator.trans('admin.util.batch_delete.deleting_hint',{name:name}) });

      $.post($btn.data('url'), {ids:ids}, function(){
        window.location.reload();
      });

    });

  }
}
