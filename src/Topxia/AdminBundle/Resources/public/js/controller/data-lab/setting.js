define(function(require, exports, module){
	var Notify = require('common/bootstrap-notify');
  exports.run = function(){
      var $swith = $('#xapi-swith'), $modal = $('#modal'), url='';
      $swith.on('click', function(){
        var isChecked = $swith.hasClass('checked');
        url = isChecked ? $swith.data('disable') : $swith.data('enabled');
        $.get(url,function(html){
          $modal.html(html).modal('show');
        })
      });

      $modal.on('click', '#xapi-setting-confirm', function(){
        var $btn = $(this);
        if ($btn.hasClass('disabled')) {
          return;
        }

        $btn.addClass('disabled');
        $.post(url, function(res){
          if (res.error) {
              Notify.danger(Translator.trans(res.error));
          }
          $swith.toggleClass('checked');
          $modal.modal('hide');
          $btn.removeClass('disabled')
        }).error(function() { 
          Notify.danger(Translator.trans('admin.data.lab.setting.error'));
         })
      })
  };
});

