import notify from 'common/notify';
let $modal = $('#modal');

export default class Detail {
  constructor() {
    this.init();
  }

  init() {
    if ($('.js-loading-text').length>0) {
      $.post($('.js-loading-text').data('url'), (resp) => {
        let html = '<img class="mll" src="data:image/png;base64,'+ resp +'" width="520px" />';
        $('.js-loading-text').remove();
        $('.js-certificate-image').html(html);
      });
    }

    let $btn = $('#cancel-certificate');
    $btn.on('click', function (e) {
      if (!confirm(Translator.trans('admin_v2.certificate.record.cancel.hint'))) {
        return false;
      }
      let url = $btn.data('url');
      $btn.button('loading');
      $.post(url, function (data) {
        $modal.modal('hide');
        notify('success', Translator.trans('admin_v2.certificate.record.cancel.success_hint'));
        window.location.reload();
      }).error(function () {
        notify('success', Translator.trans('admin_v2.certificate.record.cancel.failure_hint'));
      });
    });
  }
}

new Detail();
