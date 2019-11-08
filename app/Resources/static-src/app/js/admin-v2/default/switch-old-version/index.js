let $switchBtn = $('#switch-old-version-btn');
$switchBtn.on('click',function (e) {
    if (!confirm(Translator.trans('admin.switch_old_version.confirm_message'))) {
        return ;
    }
    $switchBtn.button('loading');
  $.post($switchBtn.data('url'),function(res){
  if(res.status == 'success' && res.url){
      window.location.href = res.url;
    }
  });

});
