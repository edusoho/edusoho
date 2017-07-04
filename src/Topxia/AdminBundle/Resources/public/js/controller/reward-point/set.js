define(function (require, exports, module) {
  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);
  var initSwitch = require('../widget/switch');
  exports.run = function () {
    initSwitch();
    let validator = new Validator({
      element: '#reward-point-set-form',
      autoSubmit: true,
    });

    $('.reward-point-amount').each(function(){
      validator.addItem({
        element: $(this),
        required: true,
        rule: 'unsigned_integer  min{min: 0} max{max: 100000}',
        display: Translator.trans('分值')
      });
    });
    $('.reward-point-daily-limit').each(function(){
      validator.addItem({
        element: $(this),
        required: true,
        rule: 'unsigned_integer min{min: 0} max{max: 100000}',
        display: Translator.trans('每日上限')
      });
    });
    $('.reward-point-value').each(function(){
          validator.addItem({
              element: $(this),
              required: true,
              rule: 'unsigned_integer  min{min: 0} max{max: 100000}',
              display: Translator.trans('分值')
          });
    });
  };

});