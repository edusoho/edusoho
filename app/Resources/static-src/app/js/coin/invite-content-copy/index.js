import notify from 'common/notify';

$('#copy').click(function () {
  $("#content").select();
  document.execCommand("Copy");
  notify('success',Translator.trans('coin.invite_url_copy_success_hint'));
});