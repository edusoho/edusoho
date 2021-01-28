$('#publishSure').on('click',function(){
  $('#publishSure').button('submiting').addClass('disabled');
  $.post($('#publishSure').data('url'), function(html) {
    $('#modal').modal('hide');
    window.location.reload();
  }).error(function(){
  });
});