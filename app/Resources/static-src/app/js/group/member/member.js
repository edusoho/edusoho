export default class Member {
  constructor() {
    this.$header = $('.js-group-header');
    this.$group = $('.js-group-section');
    this.init();
  }

  init() {
    this.bindEvent();
  }

  bindEvent() {
    this.$header.on('click', '#add-btn', (event) => this.addGroup(event));
    this.$header.on('click', '#exit-btn', (event) => this.exitGroup(event));
    this.$group.on('click', '#delete-btn', () => this.deleteMember());
    this.$group.on('click', '#remove-admin-btn', () => this.removeAdminer());
    this.$group.on('click', '#set-admin-btn', () => this.setAdminer());
  }

  addGroup(event) {
    $(event.target).addClass('disabled');
    this.btnOperated(event);
  }

  exitGroup(event) {
    if (!confirm(Translator.trans('group.manage.member_exit_hint'))) {
      return false;
    }
    this.btnOperated(event);
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

  setAdminer() {
    const tip = {
      choose: 'group.manage.choose_setting_member_hint',
      confirm: 'group.manage.setting_member_permission_hint',
      error: 'site.save_error_hint',
    };
    const url = $('#set-admin-url').attr('value');
    const $memberForm = $('#member-form');
    this.adminerOperated(tip ,url, $memberForm);
  }

  removeAdminer() {
    const tip = {
      choose: 'group.manage.choose_setting_member_hint',
      confirm: 'group.manage.cancel_member_permission',
      error: 'site.save_error_hint',
    };
    const url = $('#admin-form').attr('action');
    const $adminForm = $('#admin-form');
    this.adminerOperated(tip, url, $adminForm);
  }

  deleteMember() {
    const tip = {
      choose: 'group.manage.delete_required_error_hint',
      confirm: 'group.manage.delete_member_hint',
      error: 'site.delete_fail_hint',
    };
    const url = $('#member-form').attr('action');
    const $memberForm = $('#member-form');
    this.adminerOperated(tip, url, $memberForm, true);
  }

  adminerOperated(tip, url, $dom, flag) {
    if ($(':checkbox:checked').length < 1) {
      alert(Translator.trans(tip.choose));
      return false;
    }
    if (!confirm(Translator.trans(tip.confirm))) {
      return false;
    }
    $.post(url, $dom.serialize(), () => {
      let successMessage = flag ? 'site.delete_success_hint' : 'site.save_success_hint';
      cd.message({ type: 'success', message: Translator.trans(successMessage) });
      setTimeout(function() { window.location.reload(); }, 1500);
    }).error(() => {
      cd.message({ type: 'danger', message: Translator.trans(tip.error) });
    });
  }
}