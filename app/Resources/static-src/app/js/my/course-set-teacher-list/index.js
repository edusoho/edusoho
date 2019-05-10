import notify from 'common/notify';

$('.js-course-sticky').on('click', function (event) {
  let $btn = $(this);
  $btn.attr('disabled', true);
  $.ajax({
    'url': $btn.data('url'),
    'type': 'post',
    success: function (response) {
      notify('success', Translator.trans('置顶成功！'));
      window.location.reload();
    },
    error: function (response) {
      $btn.attr('disabled', false);
    }
  });
});

$('.js-course-unsticky').on('click', function () {
  let $btn = $(this);
  $btn.attr('disabled', true);
  $.ajax({
    'url': $btn.data('url'),
    'type': 'post',
    success: function (response) {
      notify('success', Translator.trans('取消置顶成功！'));
      window.location.reload();
    },
    error: function (response) {
      $btn.attr('disabled', false);
    }
  });
});