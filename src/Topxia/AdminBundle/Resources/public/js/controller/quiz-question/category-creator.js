define(function(require, exports, module) {
  
    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');

    exports.run = function() {
        var $form = $('#category-form');

        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                $('#category-create-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html) {
                  var id = '#' + $(html).attr('id'),
                      $item = $(id);
                  if ($item.length) {
                      $item.replaceWith(html);
                      Notify.success('保存成功');
                  } else {
                      $(".tbady-category").append(html);
                      $(".tbady-category").find('.empty').parents('tr').remove();
                      Notify.success('添加成功');
                  }
                  $form.parents('.modal').modal('hide');
              });

            }
        });

        validator.addItem({
            element: '#category-name-field',
            required: true,
            rule: 'maxlength{max:100}'
        });

        
    };

});