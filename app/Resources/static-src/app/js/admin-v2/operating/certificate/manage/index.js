import notify from 'common/notify';

export default class List {
  constructor() {
    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    let $table = $('#certificate-table');

    $table.on('click', '.close-certificate', function () {
      if (!confirm(Translator.trans('admin_v2.certificate.close.hint'))) {
        return;
      }
      $.post($(this).data('url'), function (result) {
        if (result) {
          notify('success', Translator.trans('admin_v2.certificate.close.success_hint'), 1);
          window.location.reload();
        } else {
          notify('danger', Translator.trans('admin_v2.certificate.close.failure_hint'), 1);
        }
      });
    });

    $table.on('click', '.publish-certificate', function () {
      if (!confirm(Translator.trans('admin_v2.certificate.publish.hint'))) {
        return;
      }
      $.post($(this).data('url'), function (result) {
        if (result) {
          notify('success', Translator.trans('admin_v2.certificate.publish.success_hint'), 1);
          window.location.reload();
        } else {
          notify('danger', Translator.trans('admin_v2.certificate.publish.failure_hint'), 1);
        }
      });
    });

    $table.on('click', '.delete-certificate', function () {
      if (!confirm(Translator.trans('admin_v2.certificate.delete.hint'))) {
        return;
      }
      $.post($(this).data('url'), function (result) {
        if (result) {
          notify('success', Translator.trans('admin_v2.certificate.delete.success_hint'), 1);
          window.location.reload();
        } else {
          notify('danger', Translator.trans('admin_v2.certificate.delete.failure_hint'), 1);
        }
      });
    });
  }
}

new List();
