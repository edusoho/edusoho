let $saveBtn = $('.js-save-btn');
let validator = $('#content_audit_form').validate({
  rules: {},
  ajax: true,
  submitSuccess(data) {
    cd.message({type: 'success', message: Translator.trans('site.save_success_hint')});
    $saveBtn.button('reset');
  }
});

$saveBtn.on('click', (event) => {
  const $this = $(event.currentTarget);
  $this.button('loading');
  $('#content_audit_form').submit();
});