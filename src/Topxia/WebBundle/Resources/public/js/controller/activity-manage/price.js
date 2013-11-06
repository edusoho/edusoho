define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);
  require('jquery.select2-css');
  require('jquery.select2');

  exports.run = function() {
    
    require('./header').run();

    var validator = new Validator({
      element: '#price-form',
      failSilently: true,
      triggerType: 'change'
    });

    validator.addItem({
      element: '[name="activity[price]"]',
      rule: 'currency'
    });

    validator.addItem({
      element: '[name="activity[onlinePrice]"]',
      rule: 'currency'
    });

  };

});