import notify from 'common/notify';

if ($('#exit-btn').length > 0) {
  $('#exit-btn').click(function() {
    if (!confirm(Translator.trans('group.manage.member_exit_hint'))) {
      return false;
    }
  });

}
$('#delete-btn').click(function() {
  if ($(':checkbox:checked').length < 1) {
    alert(Translator.trans('group.manage.delete_required_error_hint'));
    return false;
  }
  if (!confirm(Translator.trans('group.manage.delete_member_hint'))) {
    return false;
  }

  $.post($('#member-form').attr('action'), $('#member-form').serialize(), function() {
    notify('success',Translator.trans('site.delete_success_hint'));
    setTimeout(function() { window.location.reload(); }, 1500);
  }).error(function() {
    notify('danger',Translator.trans('site.delete_fail_hint'));
  });
});

$('#set-admin-btn').click(function() {
  if ($(':checkbox:checked').length < 1) {
    alert(Translator.trans('group.manage.choose_setting_member_hint'));
    return false;
  }
  if (!confirm(Translator.trans('group.manage.setting_member_permission_hint'))) {
    return false;
  }

  $.post($('#set-admin-url').attr('value'), $('#member-form').serialize(), function() {
    notify('success',Translator.trans('site.save_success_hint'));
    setTimeout(function() { window.location.reload(); }, 1500);

  }).error(function() {

  });

});

$('#remove-admin-btn').click(function() {
  if ($(':checkbox:checked').length < 1) {
    alert(Translator.trans('group.manage.choose_setting_member_hint'));
    return false;
  }
  if (!confirm(Translator.trans('group.manage.cancel_member_permission'))) {
    return false;
  }

  $.post($('#admin-form').attr('action'), $('#admin-form').serialize(), function() {
    notify('success',Translator.trans('site.save_success_hint'));
    setTimeout(function() { window.location.reload(); }, 1500);

  }).error(function() {

  });


});