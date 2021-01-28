define(function(require, exports, module) {
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);
  var Notify = require('common/bootstrap-notify');

  exports.run = function() {

    var validator = new Validator({
      element: '#refund-form',
    });

    validator.addItem({
      element: '[name=maxRefundDays]',
      rule: 'unsigned_integer'
    });
  };

});