define(function(require, exports, module) {
  exports.run = function() {
    $('.js-sts-switch input[type=checkbox]').click(function() {
      var data={
        app_enabled: 0,
        h5_enabled: 0,
        pc_enabled: 0
      };
      var formData = $('#face_identify').serializeArray();
      if (formData) {
        for (var i = formData.length - 1; i >= 0; i--) {
          if(formData[i].name != 'enabled') {
            data[formData[i].name] = formData[i].value;
          }
        }
      }
      $.post(document.location.href, data, function(data){
        //document.location.reload();
      });
    });
  };
});