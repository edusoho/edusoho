define(function(require, exports, module) {
  exports.run = function() {
    $('input[name=enabled]').click(function() {
      var $this = $(this);
      var $parent = $this.parent();
      var reverseFaceIdentify = 0;
      if ($this.is(':checked')) {
          reverseFaceIdentify = $this.val();
      }
      console.log(reverseFaceIdentify);
      $.post(document.location.href, { enabled: reverseFaceIdentify }, function(data) { 
        if ($parent.hasClass('checked')) {
          $parent.removeClass('checked');
        } else {
          $parent.addClass('checked');
        }
        document.location.reload();
      });
    });
  };
});
