export default class Member {
  constructor() {
    this.$group = $('.js-group-section');
    this.init();
  }

  init() {
    this.bindEvent();
  }

  bindEvent() {
    this.$group.on('click', '#delete-btn', () => this.deleteMember());
    this.$group.on('click', '#remove-admin-btn', () => this.removeAdminer());
    this.$group.on('click', '#set-admin-btn', () => this.setAdminer());
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