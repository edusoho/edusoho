$('input[type=radio][name=enable]').change(function() {
  let enable = $('input[type=radio][name=enable]:checked').val();
  if (enable === '1') {
    $('#enableTips').show()
    $('#disableTips').hide()
  }
  if (enable === '0') {
    $('#enableTips').hide()
    $('#disableTips').show()
  }
});