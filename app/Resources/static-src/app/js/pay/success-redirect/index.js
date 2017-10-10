let item = $('.js-turn');
countDown(5);
function countDown(num) {
  item.find('.js-countdown').text(num);
  if (--num > 0) {
    setTimeout(function () { countDown(num); }, 1000);
  } else {
    window.location.href = item.attr('data-url');
  }
}