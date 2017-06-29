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

                title: '删除成员',
                url: urls.memberDeleteUrl,              
                progressRange: [3, 20]
            },
            {
                title: '删除相关推荐课程',
                url: urls.recommendDeleteUrl,               
                progressRange: [21, 40]
            },
            {
                title: '删除课时资料',
                url: urls.materialDeleteUrl,
                progressRange: [41,60]
            }, 
            {
                title: '删除课时',
                url: urls.lessonDeleteUrl,
                progressRange: [61,80]
            },      
            {
                title: '删除课程',
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
                        $('.modal-title').text('删除课程');
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
                        $('#delete-form').find('.help-block').show().text('验证密码错误');
                    }
                });
            }
        });

        validator.addItem({
            element: '[name=password]',
            required: true,
            rule: 'minlength{min:5} maxlength{max:20}',
            display:'密码'
        });

        progressBar.on('completed', function() {
            progressBar.deactive();
            progressBar.text('课程删除成功');
            $("#delete-hint").hide();
            $("#finish-delete-btn").show();
            var courseId = $("input[name='courseId']").val();
            $('#course-tr-'+courseId+'').remove();
        });
    }

    function exec(title, url, progressBar, startProgress, endProgress) {

            progressBar.setProgress(startProgress, '正在' + title);
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
                    progressBar.setProgress(endProgress, title + '完成');
                    $(document).dequeue('delete_step_queue');
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                progressBar.error( title +  '时，发生了未知错误。');
                $(document).clearQueue('delete_step_queue');
            });
    }
});
