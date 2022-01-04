import notify from 'common/notify';

class RecommendList {
  constructor() {
    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    $('#group-recommend-table').on('click', '.cancel-recommend-group', e => {
      const $target = $(e.currentTarget);
      $.post($target.data('url'), function (resp) {
        if (resp) {
          $target.parents('tr').remove();
          notify('success', Translator.trans('admin.group.operating_success_hint', {trigger: $target.attr('title')}));
        }
      });
    });
  }
}

new RecommendList();