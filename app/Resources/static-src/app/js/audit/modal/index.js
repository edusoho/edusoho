$('.js-confirm-submit').click((e) => {
  $.post($(e.currentTarget).data('url'), $('#operate-confirm-form').serialize(), function () {
    $('#modal').modal('hide');
    cd.message({ type: 'success', message: Translator.trans('admin_v2.operation.user_content_audit.tip.message') });
    window.location.reload();
  });
});