define(function(require, exports, module) {

  exports.run = function() {
    $('.js-agree-radio').change(function () {
      if ($(this).prop('checked')) {
        $('.js-upgrade-btn').removeClass('disabled');
      } else {
        $('.js-upgrade-btn').addClass('disabled');
      }
    });

    $('.js-upgrade-btn').click(function () {
      $.post($(this).data('url'), function (html) {
        $('.modal').html(html);
      });
    });
  };

});