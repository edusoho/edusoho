$('input[type=radio][name=enable]').change(function() {
  let enable = $('input[type=radio][name=enable]:checked').val();
  if (enable === '1') {
    $('#enableTips').removeClass('hidden');
    $('#disableTips').addClass('hidden');
  }
  if (enable === '0') {
    $('#enableTips').addClass('hidden');
    $('#disableTips').removeClass('hidden');
  }
});