$('[data-toggle="popover"]').popover();

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

$('input[name="mode"]').change((e) => {
  $('.js-auto-audit-mode-tips').html(Translator.trans('admin_v2.system.user_content_control.content_audit_setting.auto_audit.' + $(e.currentTarget).val() + '.not_allow.tips'));
});