var toggleBtn = $('#class-teacher-column .show-more');

toggleBtn.data('toggle', true);

toggleBtn.click(function () {
  var btn = $(this);

  if (btn.data('toggle')) {

    $(this).siblings('ul').animate({
      'max-height': '2160px',
    }, 300);
    $(this).find('.fa').removeClass('fa-angle-down').addClass('fa-angle-up');
    btn.data('toggle', false);

  } else {
    $(this).siblings('ul').animate({
      'max-height': '324px'
    }, 300);
    $(this).find('.fa').removeClass('fa-angle-up').addClass('fa-angle-down');
    btn.data('toggle', true);
  }

});

var $teacherDiv = $('#class-teacher-column');
$teacherDiv.on('click', '.follow-btn', function () {
  var $btn = $(this);
  $.post($btn.data('url'), function () {
  }).always(function () {
    $btn.hide();
    $teacherDiv.find('.unfollow-btn').show();
  });
}).on('click', '.unfollow-btn', function () {
  var $btn = $(this);
  $.post($btn.data('url'), function () {
  }).always(function () {
    $btn.hide();
    $teacherDiv.find('.follow-btn').show();
  });
});