//copy from old admin admin-app.js  path: src/Topxia/AdminBundle/Resources/public/js/admin-app.js
import 'common/bootstrap-modal-hack';
import Notify from 'common/notify';
$('[data-toggle="tooltip"]').tooltip({html: true});

$('[data-toggle="popover"]').popover({
  html: true,
  trigger: 'hover',
});

$('.shortcuts').on('click', '.shortcut-add', function() {
  Notify.success(Translator.trans('admin.admin_app.shortcut_add_success'));

  let title = $(document).attr('title');

  title = title.split('|');

  const params = {
    title: title[0],
    url: window.location.pathname + window.location.search
  };
  $.post($(this).data('url'), params, function() {
    window.location.reload();
  });
});

$('.shortcuts').on('click', '.glyphicon-remove-circle', function() {
  Notify.success(Translator.trans('admin.admin_app.glyphicon_remove_circle_success'));
  $.post($(this).data('url'), function() {
    window.location.reload();
  });
});

$(document).ajaxSend(function(a, b, c) {
  if (c.type == 'POST') {
    b.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
  }
});

if($('.js-update-modal').length) {
  $('.js-update-modal').modal('show');
}

$.ajax('/online/sample');
