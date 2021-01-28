if ($('[name=access-intercept-check]').length > 0) {
  $('.topic-list').on('click', '.title', function (e) {
    var $that = $(this);
    e.preventDefault();
    $.get($('[name=access-intercept-check]').val(), function (response) {
      if (response) {
        window.location.href = $that.attr('href');
        return;
      }

      $('.access-intercept-modal').modal('show');
    }, 'json');
  });
}

