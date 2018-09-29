define(function(require, exports, module) {
  exports.run = function() {

    $('input[name=enabled]').click(function() {
      var $this = $(this);
      var $parent = $this.parent();
      var faceIdentify = $this.val();
      var reverseFaceIdentify = faceIdentify == 1 ? 0 : 1;
      var data = {};
      $('.js-sts-checkbox').each(function() {
        if ($(this).is(':checked')) {
          data[$(this).attr('name')] = $(this).val();
        }
      });
      data.enabled = reverseFaceIdentify;
      $.post(document.location.href, data, function(data) { 
        if ($parent.hasClass('checked')) {
          $parent.removeClass('checked');
          $('.js-sts-checkbox').prop('disabled', true);
          $('.js-sts-label').addClass('text-muted');
        } else {
          $parent.addClass('checked');
          $('.js-sts-label').removeClass('text-muted');
          $('.js-sts-checkbox').prop('disabled', false);
        }

        $this.val(reverseFaceIdentify);
      });
    });


    $('.js-sts-switch input[type=checkbox]').click(function() {
      var data={
        app_enabled: 0,
        wechat_enabled: 0,
        pc_enabled: 0
      };
      var formData = $('#face_identify').serializeArray();
      if (formData) {
        for (var i = formData.length - 1; i >= 0; i--) {
          data[formData[i].name] = formData[i].value;
        }
      }
      data.enabled = $('.js-switch-input').val();
      $.post(document.location.href, data, function(data){
        //document.location.reload();
      });
    });
  };
});