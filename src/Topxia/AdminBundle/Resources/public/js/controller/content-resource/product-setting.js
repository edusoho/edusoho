define(function(require, exports, module) {

  exports.run = function() {
    $('.js-show-tips').text($('input[name="type"]:checked').data('tip'));

    $('input[name="type"]').change(function () {
      $('.js-show-tips').text($('input[name="type"]:checked').data('tip'));
    });
  };

});