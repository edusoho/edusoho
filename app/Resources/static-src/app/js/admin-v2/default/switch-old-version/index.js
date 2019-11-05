let $switchBtn = $('#switch-old-version-btn');
$switchBtn.on('click',function (e) {
    $switchBtn.button('loading');
  $.post($switchBtn.data('url'),function(res){
  if(res.status == 'success' && res.url){
      window.location.href = res.url;
    }
  });

});
