import notify from 'common/notify';

export default class ChangeStatus {
  constructor() {
    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {

    $('#close-collect').on('click', function () {
      if (!confirm(Translator.trans('admin_v2.information_collect.close.hint'))) {
        return;
      }
      $.post($(this).data('url'), function (result) {
        if (result) {
          notify('success', Translator.trans('admin_v2.information_collect.close.success_hint'), 1);
          window.location.reload();
        } else {
          notify('danger', Translator.trans('admin_v2.information_collect.close.failure_hint'), 1);
        }
      });
    });

    $('#open-collect').on('click', function () {
      if (!confirm(Translator.trans('admin_v2.information_collect.open.hint'))) {
        return;
      }
      $.post($(this).data('url'), function (result) {
        if (result) {
          notify('success', Translator.trans('admin_v2.information_collect.open.success_hint'), 1);
          window.location.reload();
        } else {
          notify('danger', Translator.trans('admin_v2.information_collect.open.failure_hint'), 1);
        }
      });
    });

  }
}

new ChangeStatus();
