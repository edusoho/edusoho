import notify from 'common/notify';

export default class ChangeStatus {
  constructor() {
    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {

    $('#close-collection').on('click', function () {
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

    $('#open-collection').on('click', function () {
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

new ChangeStatus();
