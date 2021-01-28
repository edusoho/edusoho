define(function (require, exports, module) {

  function initSwitch() {
    $('.es-switch').on('click' , function (e) {
      var $input = $(this).find('.es-switch__input');
      var ToggleVal = $input.val() == $input.data('open')? $input.data('close') : $input.data('open');
      $input.val(ToggleVal);
      $(this).toggleClass('is-active');
    });
  }

  module.exports = initSwitch;
});