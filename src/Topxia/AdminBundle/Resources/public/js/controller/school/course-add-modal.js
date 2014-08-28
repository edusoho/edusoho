define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    
    exports.run = function() {


        $('#course-table tbody tr').on('click',function(){
            $('#course-table tbody').find('.success').removeClass('success');
            $(this).addClass('success');
            $('#select-area').attr('class','show');
            $('#title-span').html($(this).data('title'));
            $('#name-span').html($(this).data('teachername'));
            $('#templateId').val($(this).data('id'));
        });

        var $modal = $('#class-create-form').parents('.modal');
        var $form = $("#class-course-add-form");

        var validator = new Validator({
            element: '#class-course-add-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                $('#class-course-add-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    Notify.success('添加课程成功');
                    window.location.href=$('#backto').data('url');
                }).error(function(){
                    Notify.danger('添加课程失败');
                });

            }
        });
    };
});