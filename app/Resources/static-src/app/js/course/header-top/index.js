import 'app/common/widget/qrcode';

let $unfavorite = $('.js-unfavorite-btn');
let $favorite = $('.js-favorite-btn');
discountCountdown();
ancelRefund();

function ancelRefund() {
  $('.cancel-refund').on('click', function () {
    if (!confirm(Translator.trans('course_set.refund_cancel_hint'))) {
      return false;
    }
    $.post($(this).data('url'), function (data) {
      window.location.reload();
    });
  });
}

function discountCountdown() {
  var remainTime = parseInt($('#discount-endtime-countdown').data('remaintime'));
  if (remainTime >= 0) {
    var endtime = new Date(new Date().valueOf() + remainTime * 1000);
    $('#discount-endtime-countdown').countdown(endtime, function (event) {
      var $this = $(this).html(event.strftime(Translator.trans('course_set.show.count_down_format_hint')));
    }).on('finish.countdown', function () {
      $(this).html(Translator.trans('course_set.show.time_finish_hint'));
      setTimeout(function () {
        $.post(app.crontab, function () {
          window.location.reload();
        });
      }, 2000);
    });
  }
}

if ($favorite.length) {
  $favorite.on('click', function () {
    $.ajax({
      type: "POST",
      data: {
        'targetType': $(this).data('targetType'),
        'targetId': $(this).data('targetId'),
      },
      beforeSend: function (request) {
        request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
        request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
      },
      url: '/api/favorite',
      success: function (resp) {
        $unfavorite.removeClass('hidden');
        $favorite.addClass('hidden');
      }
    });
  });
}

if ($unfavorite.length) {
  $unfavorite.on('click', function () {
    $.ajax({
      type: "DELETE",
      beforeSend: function (request) {
        request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
        request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
      },
      url: '/api/favorite?' + 'targetType=' + $(this).data('targetType') + '&targetId=' + $(this).data('targetId'),
      success: function (resp) {
        $favorite.removeClass('hidden');
        $unfavorite.addClass('hidden');
      }
    });
  });
}

const fixButtonPosition = () => {
  const $target = $('.js-course-detail-info');
  const height = $target.height();
  const $btn = $('.js-course-header-operation');
  if (height > 240) {
    $btn.removeClass('course-detail-info__btn');
  }
};

$(document).ready(() => {
  fixButtonPosition();
});