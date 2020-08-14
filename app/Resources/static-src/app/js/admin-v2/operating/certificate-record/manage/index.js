import notify from 'common/notify';

export default class List {
  constructor() {
    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    let $table = $('#record-table');

    $table.on('click', '.js-cancel', function () {
      if (!confirm(Translator.trans('admin_v2.certificate.record.cancel.hint'))) {
        return;
      }
      $.post($(this).data('url'), function (result) {
        if (result) {
          notify('success', Translator.trans('admin_v2.certificate.record.cancel.success_hint'), 1);
          window.location.reload();
        } else {
          notify('danger', Translator.trans('admin_v2.certificate.record.cancel.failure_hint'), 1);
        }
      });
    });
  }
}

new List();
