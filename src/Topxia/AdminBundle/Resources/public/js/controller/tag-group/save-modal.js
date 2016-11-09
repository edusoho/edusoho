define(function(require, exports, module) {
  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  exports.run = function() {
    var $form = $('#tag-group-form');
    var $modal = $form.parents('.modal');
    var $table = $('#tag-group-table');

    var validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
            return ;
        }

        $('#tag-group-create-btn').button('submiting').addClass('disabled');

        $.post($form.attr('action'), $form.serialize(), function(html){
            var $html = $(html);
            if ($table.find( '#' +  $html.attr('id')).length > 0) {
                $('#' + $html.attr('id')).replaceWith($html);
                Notify.success(Translator.trans('标签组更新成功！'));
            } else {
                if ($('.empty')) {
                  $('.empty').remove();
                }
                $table.find('tbody').prepend(html);
                Notify.success(Translator.trans('标签组添加成功!'));
            }
            $modal.modal('hide');
        });
      }
    });

    validator.addItem({
        element: '#tag-group-name-field',
        required: true,
        rule: 'remote'
    });

        $modal.find('.delete-tag-group').on('click', function() {
            if (!confirm(Translator.trans('真的要删除该标签组吗？'))) {
                return ;
            }

            var trId = '#tag-group-tr-' + $(this).data('tagGroupId');
            $.post($(this).data('url'), function(html) {
                $modal.modal('hide');
                $table.find(trId).remove();
            });

        });
  };
});