define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);

  exports.run = function() {
    var $form = $('#order-adjust-form');

    var validator = new Validator({
      element: $form,
      autoSubmit: false,
      autoFocus: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
          return ;
        }

        $('#refund-confirm-btn').button('submiting').addClass('disabled');
      }
    });

    validator.getExplain = function (ele) {
      return $('.js-display-error ');
    };

    validator.addItem({
      element: 'input[name=adjust-by-amount]',
      required: false,
      rule: 'decimal'
    });

    validator.addItem({
      element: 'input[name=adjust-by-discount]',
      required: false,
      rule: 'decimal max{max: 10}',
      display: '折扣'
    });

    $form.on('change', '.js-adjust-discount', function () {
      $el = $(this);
    });

    $form.on('change', '.js-adjust-amount', function () {
      $el = $(this);
    });

    function changeAmount() {
      "use strict";

    }

  };

});