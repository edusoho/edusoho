define(function(require, exports, module) {
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);
  var Notify = require('common/bootstrap-notify');

  exports.run = function() {
    Validator.addRule('name_max', function (options) {
      let maxLength = true;
      if($('.js-select-edit-content').hasClass('hidden')){
        return maxLength;
      }
      let values = $(options.element).val().split("\n");
      values.map(function (value, index, array) {
        if (calculateByteLength(value) > 20) {
          maxLength = false;
        }
      });
      return maxLength;
    }, Translator.trans('user_field.select_type.tip'));

    var $form = $('#field-save-form');
    var validator = new Validator({
      element: $form,
      onFormValidated: function(error, results, $form) {
        if (error) {
          return false;
        }
        $('#save-btn').button('submiting').addClass('disabled');
      }
    });
    if(!$('.js-select-edit-content').hasClass('hidden')) {
      validator.addItem({
        element: '#select-list-edit',
        required: true,
        rule: 'name_max'
      });
    }

    function calculateByteLength(string) {
      let length = string.length;
      for (let i = 0; i < string.length; i++) {
        if (string.charCodeAt(i) > 127)
          length++;
      }
      return length;
    }

    validator.addItem({
      element: '[name="title"]',
      required: true,
      rule:'minlength{min:2} maxlength{max:20}'
    });

    validator.addItem({
      element: '[name="seq"]',
      required: true,
      rule:'positive_integer'
    });

  };

});