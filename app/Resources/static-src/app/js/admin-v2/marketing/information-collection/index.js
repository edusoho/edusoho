import notify from 'common/notify';

export default class List {
  constructor() {
    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    let $table = $('#collection-table');

    $table.on('click', '.close-collection', function () {
      if (!confirm(Translator.trans('admin_v2.information-collection.close.hint'))) {
        return;
      }
      $.post($(this).data('url'), function (result) {
        if (result) {
          notify('success', Translator.trans('admin_v2.information-collection.close.success_hint'), 1);
          window.location.reload();
        } else {
          notify('danger', Translator.trans('admin_v2.information-collection.close.failure_hint'), 1);
        }
      });
    });

    $table.on('click', '.open-collection', function () {
      if (!confirm(Translator.trans('admin_v2.information-collection.open.hint'))) {
        return;
      }
      $.post($(this).data('url'), function (result) {
        if (result) {
          notify('success', Translator.trans('admin_v2.information-collection.open.success_hint'), 1);
          window.location.reload();
        } else {
          notify('danger', Translator.trans('admin_v2.information-collection.open.failure_hint'), 1);
        }
      });
    });

  }
}

new List();
