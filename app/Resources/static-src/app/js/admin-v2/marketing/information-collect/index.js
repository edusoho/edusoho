import notify from 'common/notify';

export default class List {
  constructor() {
    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    let $table = $('#collect-table');

    $table.on('click', '.close-collect', function () {
      if (!confirm(Translator.trans('admin_v2.information-collect.close.hint'))) {
        return;
      }
      $.post($(this).data('url'), function (result) {
        if (result) {
          notify('success', Translator.trans('admin_v2.information-collect.close.success_hint'), 1);
          window.location.reload();
        } else {
          notify('danger', Translator.trans('admin_v2.information-collect.close.failure_hint'), 1);
        }
      });
    });

    $table.on('click', '.open-collect', function () {
      if (!confirm(Translator.trans('admin_v2.information-collect.open.hint'))) {
        return;
      }
      $.post($(this).data('url'), function (result) {
        if (result) {
          notify('success', Translator.trans('admin_v2.information-collect.open.success_hint'), 1);
          window.location.reload();
        } else {
          notify('danger', Translator.trans('admin_v2.information-collect.open.failure_hint'), 1);
        }
      });
    });

  }
}

new List();
