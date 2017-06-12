define(function (require, exports, module) {
  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  exports.run = function () {

    let validator = new Validator({
      element: '#reward-point-set-form',
      autoSubmit: false,
    });

    $('.reward-point-amount').each(function(){
      validator.addItem({
        element: $(this),
        required: true,
        rule: 'unsigned_integer',
        display: Translator.trans('分值')
      });
    });

    $('.reward-point-daily-limit').each(function(){
      validator.addItem({
        element: $(this),
        required: true,
        rule: 'unsigned_integer',
        display: Translator.trans('每日上限')
      });
    });

  };

});