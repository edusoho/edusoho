import notify from 'common/notify';

export default class List {
  constructor() {
    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    let $table = $('#template-table');

    $table.on('click', '.delete-template', function () {
      if (!confirm(Translator.trans('site.delete.confirm_hint'))) {
        return;
      }
      $.post($(this).data('url'), function (result) {
        if (result) {
          notify('success', Translator.trans('site.delete_success_hint'), 1);
          window.location.reload();
        } else {
          notify('danger', Translator.trans('site.delete_fail_hint'), 1);
        }
      });
    });
  }
}

new List();
