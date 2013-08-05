define(function(require, exports, module) {
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);

  exports.run = function() {

    var validator = new Validator({
        element: '#course-create-form'
    });
    
    validator.addItem({
      element: '[name="message[receiver]"]',
      required: true,
      rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:14} remote'
    });

     validator.addItem({
            element: '[name="message[content]"]',
            required: true,
            rule: 'maxlength{max:255}'
        });

    $('#modal').modal('show');
  }

});