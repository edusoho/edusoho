$('.js-confirm-submit').click((e) => {
  $.post($(e.currentTarget).data('url'), $('#operate-confirm-form').serialize(), function () {
    $('#modal').modal('hide');
    cd.message({ type: 'success', message: Translator.trans('admin_v2.operation.audit_center.batch_operate_success_tips') });
    window.location.reload();
  });
});