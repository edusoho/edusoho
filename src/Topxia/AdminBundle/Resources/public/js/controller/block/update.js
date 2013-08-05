define(function(require, exports, module) {

    require('ckeditor');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var $form = $('#block-form');
        var $modal = $form.parents('.modal');
        var $table = $('#block-table');

        CKEDITOR.replace('form_content', {
                    // height: '50%',
                    // resize_enabled: false,
                    // forcePasteAsPlainText: true,
                    toolbar: 'Mini',
                    removePlugins: 'elementspath',
                });

        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }

                CKEDITOR.instances['form_content'].updateElement();
                $.post($form.attr('action'), $form.serialize(), function(response){
                    if (response.status == 'ok') {
                        var $html = $(response.html);
                        if ($table.find( '#' +  $html.attr('id')).length > 0) {
                            $('#' + $html.attr('id')).replaceWith($html);
                            toastr.success('更新成功！');
                        } else {
                            $table.find('tbody').prepend(response.html);
                            toastr.success('提交成功!');
                        }
                        $modal.modal('hide');
                    } else {
                        var errorMsg = '添加失败：' + ((response.error && response.error.message) ? response.error.message : '');
                        toastr.error(errorMsg);
                    }
                }, 'json'); 
            }
        });

        $('.btn-recover-content').on('click', function(){
              var text = $(this).parents('tr').find('.data-role-content').text();
              CKEDITOR.instances.form_content.setData(text);
        });
    };

});