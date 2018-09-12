define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var $form = $('#course-copy-form');
        var $modal = $form.parents('.modal');
        $("input[name='title']").on('input',function(){
              var element = $(this);
              var comfirm_title = element.val();
              $("#course_title").attr('value',comfirm_title);
        });

         $('#course-copy-btn').on("click",function(){
            if($("#course_title").attr('value') == ''){
                return false;
            }else{
                validator.removeItem('#course_title');
                return true;
            }
         });

        var validator = new Validator({
                element: $form,
                autoSubmit: false,
                onFormValidated: function(error, results, $form) {
                console.log(error);
                $('#course-copy-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    Notify.success(Translator.trans('admin.course.copy_success_hint'));
                    window.location.reload();
                }).error(function(){
                    Notify.danger(Translator.trans('admin.course.copy_fail_hint'));
                });

            }
        });

        validator.addItem({
            element: '#course_title',
            required: true,
            errormessageRequired: Translator.trans('validate.title_required_hint')
        });


        
    };

});