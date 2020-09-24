import notify from 'common/notify';

export default class List {
  constructor() {
    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {

    $('#collection-close').on('click', function () {
      if (!confirm(Translator.trans('admin_v2.information-collection.close.hint'))) {
        return;
      }
      $.get($(this).data('url'), function (result) {
        if (result) {
          notify('success', Translator.trans('admin_v2.information-collection.close.success_hint'), 1);
          window.location.reload();
        } else {
          notify('danger', Translator.trans('admin_v2.information-collection.close.failure_hint'), 1);
        }
      });
    });

  }
}

new List();
