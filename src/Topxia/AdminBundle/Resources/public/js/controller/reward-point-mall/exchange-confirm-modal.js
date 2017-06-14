define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');

  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  exports.run = function() {

    var $table = $('#exchange-table');
    var $modal = $('#message-form').parents('.modal');
    var validator = new Validator({
      element: $('#message-form'),
      autoSubmit: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
          return false;
        }

        $('#exchange-order-btn').button('submitting').addClass('disabled');

        $.post($form.attr('action'), $form.serialize(), function(html) {
          var $tr = $(html);
          $('#' + $tr.attr('id')).replaceWith(html);
          Notify.success(Translator.trans('操作成功！'));

          $modal.modal('hide');
        }).error(function(){
          Notify.danger(Translator.trans('操作失败'));
        });
      }
    });

    validator.addItem({
      element: '[name="message"]',
      rule: 'byte_maxlength{max:50}'
    });
  };

});
