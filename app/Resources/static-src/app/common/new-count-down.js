export const countDown = ($dom, $text, num, callback = function(){}) => {
  $dom.addClass('disabled').attr('disabled', true);
  if (!$text.data('count-down-text')) {
    $text.data('count-down-text', $text.text());
  }
  $text.text(num+' 秒后重新获取');

  if (--num < 0) {
    $dom.removeClass('disabled').attr('disabled', false);
    $text.text($text.data('count-down-text'));
    callback();
    return;
  }

  setTimeout(() => {
    countDown($dom, $text, num, callback);
  }, 1000);
};