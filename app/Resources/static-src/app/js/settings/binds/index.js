import notify from 'common/notify';

$('.js-unbind-btn').on('click', function() {
  let $this = $(this);
  let url = $this.data('url');
  $.confirm({
    title: Translator.trans('user.settings.unbind_title'),
    text: Translator.trans('user.settings.unbind_content'),
    confirm() {
      $.get(url, function (data) {
        notify('success', Translator.trans(data.message));
        setTimeout(function() {
          window.location.reload();
        }, 3000);
      });
    }
  })
});