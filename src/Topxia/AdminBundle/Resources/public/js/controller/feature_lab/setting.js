define(function(require, exports, module) {
  exports.run = function() {
    $('input[name=face_identify]').click(function() {
      var $this = $(this);
      var $parent = $this.parent();
      var faceIdentify = $this.val();
      var reverseFaceIdentify = faceIdentify == 1 ? 0 : 1;
      $.post(document.location.href, { face_identify: reverseFaceIdentify }, function(data) { 
        if ($parent.hasClass('checked')) {
          $parent.removeClass('checked');
        } else {
          $parent.addClass('checked');
        }
        $this.val(reverseFaceIdentify);
        document.location.reload();
      });
    });
  };
});
