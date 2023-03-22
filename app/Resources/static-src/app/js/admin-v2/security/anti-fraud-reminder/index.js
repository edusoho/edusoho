$('input[type=radio][name=enable]').change(function() {
  let enable = $('input[type=radio][name=enable]:checked').val();
  if (enable === '1') {
    $('#enableTips').removeClass('hidden');
    $('#disableTips').addClass('hidden');
    $('.remind_setting').removeClass('hidden');
  }
  if (enable === '0') {
    $('#enableTips').addClass('hidden');
    $('#disableTips').removeClass('hidden');
    $('.remind_setting').addClass('hidden');
  }
});

$('.js-tooltip-twig-widget').find('.js-twig-widget-tips').each(function () {
  var $self = $(this);
  $self.popover({
      html: true,
      trigger: 'focus', //'hover','click'
      placement: $self.data('placement'),//'bottom',
      content: $self.next(".js-twig-widget-html").html()
  });
});
