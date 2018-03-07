$('a[data-role="announcement-modal"]').click(function () {
  var $modal = $('#modal');
  $modal.html('').load($(this).data('url'));
});

$('.announcement-list').on('click', '[data-role=delete]', function () {
  if (confirm(Translator.trans('announcement.delete_hint'))) {
    $.post($(this).data('url'), function () {
      window.location.reload();
    });
  }
  return false;
});

if ($('.alert-edit').height()) {
  var alertHeader = $('.alert-edit .alert-header');
  var alertIcon = alertHeader.find('.icon-click');

  if (alertIcon.hasClass('es-icon-chevronright')) {
    alertIcon.data('toggle', true);

  } else {
    alertIcon.data('toggle', false);
  }

  alertHeader.click(function () {
    $(this).siblings('.details').animate({
      // height:'toggle',
      visibility: 'toggle',
      opacity: 'toggle',
      // speed: 'fast',
      easing: 'linear'
    });

    var btn = $(this).find('.icon-click');

    if (btn.data('toggle') && btn.parents('.alert-header').siblings('.details').height()) {
      btn.addClass('es-icon-keyboardarrowdown').removeClass('es-icon-chevronright');
      btn.data('toggle', false);

    } else {
      btn.addClass('es-icon-chevronright').removeClass('es-icon-keyboardarrowdown');
      btn.data('toggle', true);
    }
  });
}

$('.annoucement-add-btn, .es-icon-edit').click(function () {
  $('#modal').modal('hide');
});
