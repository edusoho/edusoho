define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');

  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  exports.run = function() {

    var $form = $('#message-form');
    var $table = $('#exchange-table');
    var $modal = $('#message-form').parents('.modal');
    var validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
          return false;
        }

        $('#exchange-order-btn').button('submitting').addClass('disabled');

        $.post($form.attr('action'), $form.serialize(), function(html) {
          var $tr = $(html);
          $('#' + $tr.attr('id')).replaceWith(html);
          console.log($form.data('flag'));
          if ($form.data('flag') == 'edit'){
            Notify.success(Translator.trans('更新发货留言成功！'));
          } else {
            Notify.success(Translator.trans('发货成功！'));
          }

          $modal.modal('hide');
        }).error(function(){
          if ($form.data('flag') == 'edit'){
            Notify.danger(Translator.trans('更新发货留言失败'));
          } else {
            Notify.danger(Translator.trans('发货失败'));
          }
        });
      }
    });

    validator.addItem({
      element: '[name="message"]',
      rule: 'byte_maxlength{max:50}'
    });
  };

});
