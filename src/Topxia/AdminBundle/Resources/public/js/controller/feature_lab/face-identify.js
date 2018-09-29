define(function(require, exports, module) {
  exports.run = function() {

    $('input[name=enabled]').click(function() {
      var $this = $(this);
      var $parent = $this.parent();
      var faceIdentify = $this.val();
      var reverseFaceIdentify = faceIdentify == 1 ? 0 : 1;
      $.post(document.location.href, { enabled: reverseFaceIdentify }, function(data) { 
        if ($parent.hasClass('checked')) {
          
          $parent.removeClass('checked');
        } else {
          $parent.addClass('checked');
        }
        $this.val(reverseFaceIdentify);
        document.location.reload();
      });
    });


    $('#face_identify input[type=checkbox]').click(function() {
      var data={
        app_enabled: 0,
        wechat_enabled: 0,
        pc_enabled: 0
      };
      var formData = $('#face_identify').serializeArray();
      console.log(formData);
      if (formData) {
        for (var i = formData.length - 1; i >= 0; i--) {
          data[formData[i].name] = formData[i].value;
        }
      }

      $.post(document.location.href, data, function(data){
        //document.location.reload();
      });
    });
  };
});