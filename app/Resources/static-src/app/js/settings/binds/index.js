import notify from 'common/notify';

$('.js-unbind-btn').on('click', function() {
  let $this = $(this);
  let url = $this.data('url');
  cd.confirm({
    title: Translator.trans('user.settings.unbind_title'),
    content: Translator.trans('user.settings.unbind_content'),
    confirmText: Translator.trans('site.confirm'),
    cancelText: Translator.trans('site.close'),
    confirm() {
      $.post(url, function (data) {
        notify('success', Translator.trans(data.message));
        setTimeout(function() {
          window.location.reload();
        }, 3000);
      });
    }
  });
});