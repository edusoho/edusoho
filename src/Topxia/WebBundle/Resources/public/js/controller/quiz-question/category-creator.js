define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');

    exports.run = function() {
        var $form = $('#category-form');
        var $modal = $form.parents('.modal');

        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                $.post($form.attr('action'), $form.serialize(), function(html) {
                  var id = '#' + $(html).attr('id'),
                      $item = $(id);
                  if ($item.length) {
                      $item.replaceWith(html);
                      Notify.success('保存成功');
                  } else {
                      $(".tbady-category").prepend(html);
                      $(".tbady-category").find('.empty').hide();
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

        $('body').find('.delete-category').on('click', function() {
            if (!confirm('真的要删除该分类及其子分类吗？')) {
                return ;
            }
            var that = $(this);
            $.post($(this).data('url'), function(html) {
                $modal.modal('hide');
                $('#category-tr-'+that.data('id')).remove();
            });

        });
    };

});