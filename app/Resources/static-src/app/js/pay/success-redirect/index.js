let item = $('.js-turn');
if ($('.js-feedback').length) {
  countDown(item.find('.js-countdown').text());
}


function countDown(num) {
  item.find('.js-countdown').text(num);
  if (--num > 0) {
    setTimeout(function () { countDown(num); }, 1000);
  } else {
    window.location.href = item.attr('data-url');
  }
}