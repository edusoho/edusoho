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

        $.post($form.attr('action'), {adjustPrice:$form.find('.js-pay-amount').html()}, function(resp) {
          window.location.reload();
        });
      }
    });

    validator.getExplain = function (ele) {
      return $('.js-display-error');
    };

    validator.addItem({
      element: 'input[name=adjust-by-price]',
      required: false,
      rule: 'currency',
      display: Translator.trans('admin.order.validate_old.adjust_price.display'),
      errormessageCurrency: Translator.trans('admin.order.validate_old.valid_price_input.message')
    });

    validator.addItem({
      element: 'input[name=adjust-by-discount]',
      required: false,
      rule: 'currency max{max: 10}',
      display: Translator.trans('admin.order.validate_old.discount.display'),
      errormessageCurrency: Translator.trans('admin.order.validate_old.valid_discount_input.message')
    });

    var originPayAmount = $form.find('.js-origin-pay-amount').data('originAmount');
    $form.on('change', '.js-adjust-price', function () {
      $el = $(this);
      var adjustPrice = parseFloat(originPayAmount) - parseFloat($el.val());
      if ($.isNumeric(adjustPrice)) {
        var discount = (adjustPrice)*10/parseFloat(originPayAmount);
        $form.find('.js-adjust-discount').val(discount.toFixed(2));
        $form.find('.js-pay-amount').text(adjustPrice.toFixed(2));
      }

    });

    $form.on('change', '.js-adjust-discount', function () {
      $el = $(this);
      var discount = $el.val();
      if ($.isNumeric(discount)) {
        var adjustPrice = ((10 - discount) * originPayAmount / 10);
        $form.find('.js-adjust-price').val(adjustPrice.toFixed(2));
        $form.find('.js-pay-amount').text((originPayAmount - adjustPrice).toFixed(2));
      }

    });

  };

});