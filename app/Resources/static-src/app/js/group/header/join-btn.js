export default class JoinGroup {
  constructor() {
    this.$header = $('.js-group-header');
    this.init();
  }

  init() {
    this.bindEvent();
  }

  bindEvent() {
    this.$header.on('click', '#add-btn', (event) => this.addGroup(event));
    this.$header.on('click', '#exit-btn', (event) => this.exitGroup(event));
  }

  addGroup(event) {
    $(event.target).addClass('disabled');
    this.btnOperated(event);
  }

  exitGroup(event) {
    cd.confirm({
      title: Translator.trans('group.manage.member_exit'),
      content: Translator.trans('group.manage.member_exit_hint'),
      okText: Translator.trans('site.confirm'),
      cancelText: Translator.trans('site.cancel'),
    }).on('ok', () => {
      this.btnOperated(event);
    });
  }

  btnOperated(event) {
    const $target = $(event.target);
    const url = $target.data('url');
    $.post(url, (data) => {
      if (data.status === 'success') {
        window.location.reload();
      } else {
        cd.message({ type: 'danger', message: Translator.trans(data.message) });
      }
    });
  }
}

