define(function(require, exports, module) {
  exports.run = function() {
    $('.js-switch-input').click(function() {
      var $this = $(this);
      var $parent = $this.parent();
      var faceIdentify = $this.val();
      var reverseFaceIdentify = faceIdentify == 1 ? 0 : 1;
      $this.val(reverseFaceIdentify);
      var data = {};
      $('.js-sts-checkbox').each(function () {
        data[$(this).attr('name')] = $(this).is(':checked') ? 1 : 0;
      });
      $('.js-switch-input').each(function () {
        data[$(this).attr('name')] = $(this).val();
      });
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
      });
    });


    $('.js-sts-switch input[type=checkbox]').click(function () {
      var data = {};
      $('.js-sts-checkbox').each(function(){
        data[$(this).attr('name')] = $(this).is(':checked') ? 1 : 0;
      });
      $('.js-switch-input').each(function () {
        data[$(this).attr('name')] = $(this).val();
      });
      $.post(document.location.href, data, function (data) {

      });
    });
  };
});