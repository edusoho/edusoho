define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var ProgressBar = require('./ProgressBar');

    exports.run = function() {

        var progressBar = new ProgressBar({
            element: '#delete-course-progress'
        });

        var $deleteBtn = $("#delete-btn");
        var urls = $deleteBtn.data();

        var steps = [
            
            {

                title: Translator.trans('admin.open_course.member_delete_title'),
                url: urls.memberDeleteUrl,              
                progressRange: [3, 20]
            },
            {
                title: Translator.trans('admin.open_course.recommend_delete_title'),
                url: urls.recommendDeleteUrl,               
                progressRange: [21, 40]
            },
            {
                title: Translator.trans('admin.open_course.material_delete_title'),
                url: urls.materialDeleteUrl,
                progressRange: [41,60]
            }, 
            {
                title: Translator.trans('admin.open_course.lesson_delete_title'),
                url: urls.lessonDeleteUrl,
                progressRange: [61,80]
            },      
            {
                title: Translator.trans('admin.open_course.course_delete_title'),
                url: urls.courseDeleteUrl,
                progressRange: [81, 100]
            }
        ];

		$form = $('#delete-form');
        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $.post($form.attr('action'), $form.serialize(), function(response) {
                    
                    if(response.success){
                        $('.modal-title').text(Translator.trans('admin.open_course.course_delete_title'));
                        $('#delete-form').addClass('hidden');
                        $('.progress').removeClass('hidden');
                        $('#delete-hint').show();
                        $('#delete-btn').hide();
                        $('.btn-closed').hide();
                        progressBar.show();
                        $.each(steps, function(i, step) {
                            $(document).queue('delete_step_queue', function() {
                                exec(step.title, step.url, progressBar, step.progressRange[0], step.progressRange[1]);
                            });
                        });
                        $(document).dequeue('delete_step_queue');
                    }else{
                        $('#delete-form').children('div').addClass('has-error');
                        $('#delete-form').find('.help-block').show().text(Translator.trans('admin.course.delete_course.check_password_fail_hint'));
                    }
                });
            }
        });

        validator.addItem({
            element: '[name=password]',
            required: true,
            rule: 'minlength{min:5} maxlength{max:20}',
            display:Translator.trans('admin.course.validate_old.password_required_hint')
        });

        progressBar.on('completed', function() {
            progressBar.deactive();
            progressBar.text(Translator.trans('admin.course.delete_success_hint'));
            $("#delete-hint").hide();
            $("#finish-delete-btn").show();
            var courseId = $("input[name='courseId']").val();
            $('#course-tr-'+courseId+'').remove();
        });
    }

    function exec(title, url, progressBar, startProgress, endProgress) {

            progressBar.setProgress(startProgress, Translator.trans('admin.open_course.delete_exec_hint', {title:title}));
            $.ajax(url, {
                async: true,
                dataType: 'json',
                type: 'POST'
            }).done(function(data, textStatus, jqXHR) {
                if(data.success){
                    progressBar.setProgress(startProgress, data.message);
                    title =  data.message;
                    exec(title, url, progressBar, startProgress, endProgress);
                }else{
                    progressBar.setProgress(endProgress, title + Translator.trans('admin.open_course.delete_finish'));
                    $(document).dequeue('delete_step_queue');
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                progressBar.error(Translator.trans('admin.open_course.delete_unknow_error_hint', {title:title}));
                $(document).clearQueue('delete_step_queue');
            });
    }
});
