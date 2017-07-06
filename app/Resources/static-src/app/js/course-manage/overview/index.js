let url = $('.js-date-change-url').data('url');
let $timeSlectBtn = $('.is-date-change');
let $tabChangBtn = $('.js-tab-change');
let ajax = false;

$timeSlectBtn.on('click', function() {
  let type = $(this).data('type');
  let time = $(this).data('time');
  $.post(url, {
    type: type,
    time: time
  }).done(() => {
    console.log('success');
  }).fail(() => {
    console.log('error');
  })
});

$tabChangBtn.on('click', function() {
  $(this).parents('.course-statictics-content').find('.js-chart-change').toggle();
  if (ajax == false) {
    $.post(url).done(() => {
      ajax = true;
    }).fail(() => {
      console.log('fail');
    })
  }
})