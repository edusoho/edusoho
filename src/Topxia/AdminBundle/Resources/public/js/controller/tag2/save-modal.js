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
                        Notify.success('标签组更新成功！');
                    } else {
                        $table.find('tbody').prepend(html);
                        Notify.success('标签组添加成功!');
                    }
                    $modal.modal('hide');
                });

            }
        });

        validator.addItem({
            element: '#tag-group',
            required: true,
            rule: 'remote'
        });

        $modal.find('.delete-tag-group').on('click', function() {
            if (!confirm('真的要删除该标签组吗？')) {
                return ;
            }

            var trId = '#tag-tr-' + $(this).data('tagId');
            $.post($(this).data('url'), function(html) {
                $modal.modal('hide');
                $table.find(trId).remove();
            });

        });

    };




});