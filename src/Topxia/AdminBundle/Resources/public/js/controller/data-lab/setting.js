define(function(require, exports, module){

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
        $.post(url, function(){
          $swith.toggleClass('checked');
          $modal.modal('hide');
        })
      })
  };
});

