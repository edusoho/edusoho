export const shortLongText  = ($element) => {
  $element.on('click', '.short-text', function() {
    var $short = $(this);
    $short.slideUp('fast').parents('.short-long-text').find('.long-text').slideDown('fast');
  });
  $element.on('click', '.long-text', function() {
    var $long = $(this);
    $long.slideUp('fast').parents('.short-long-text').find('.short-text').slideDown('fast');
  });
};