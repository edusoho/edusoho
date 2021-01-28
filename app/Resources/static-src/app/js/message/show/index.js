import notify from 'common/notify';
let $replyBtn = $('#course-reply-btn');

$('#message-reply-form').on('click', '#course-reply-btn', function (e) {
  $replyBtn.addClass('disabled').attr('disabled', true);
  if ($('#message_reply_content').val().length >= 500) {
    notify('danger',Translator.trans('notify.private_message_maxlength.message'));
    $replyBtn.removeClass('disabled').attr('disabled', false);
    return false;
  }

  if ($.trim($('#message_reply_content').val()).length == 0) {
    notify('danger',Translator.trans('validate.empty_content_hint'));
    $replyBtn.removeClass('disabled').attr('disabled', false);
    return false;
  }

  $.post($('#message-reply-form').attr('action'), $('#message-reply-form').serialize())
    .success(function(response) {
      $('.message-list').prepend(response.html);
      $('#message_reply_content').val('');
      $replyBtn.removeClass('disabled').attr('disabled', false);
    })
    .error(function(response) {
      $replyBtn.removeClass('disabled').attr('disabled', false);
    });

  return false;
});

$('.message-list').on('click', '.delete-message', function (e) {

  if ($('.message-list').find('.message-me').length == 1) {
    if (!confirm(Translator.trans('confirm.last_private_message_delete.message'))) {
      return false;
    }
  } else {
    if (!confirm(Translator.trans('confirm.private_message_delete.message'))) {
      return false;
    }
  }

  var $item = $(this).parents('.media');
  $.post($(this).data('url'), function () {
    if ($('.message-list').find('.message-me').length == 1) {
      window.location.href = $item.attr('parent-url');
    }
    $item.remove();
  });
});


$('textarea').bind('input propertychange', function () {
  if ($('#message_reply_content').val().length > 0) {
    $('#course-reply-btn').removeClass('disabled');
  } else {
    $('#course-reply-btn').addClass('disabled');
  }
});

