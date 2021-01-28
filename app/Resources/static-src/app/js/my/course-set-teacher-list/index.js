$('.js-course-sticky').on('click', function (event) {
  let $btn = $(this);
  $btn.attr('disabled', true);
  $.ajax({
    'url': $btn.data('url'),
    'type': 'post',
    success: function (response) {
      cd.message({type:'success', message: Translator.trans('course.stick.success')});
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
      cd.message({type:'success', message: Translator.trans('course.cancel.stick.success')});
      window.location.reload();
    },
    error: function (response) {
      $btn.attr('disabled', false);
    }
  });
});