$('.js-reset-secret').on('click', function () {
  if (!confirm(Translator.trans('admin.setting.api_secret.reset_confirm_message'))) {
    return false;
  } else {
    window.location.href = $(this).data('url');
  }
});