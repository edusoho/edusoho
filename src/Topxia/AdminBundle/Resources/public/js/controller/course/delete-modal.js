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
                title: Translator.trans('删除问题'),
                url: urls.questionDeleteUrl,
                progressRange: [3, 6]
            },
            {
                title: Translator.trans('删除试卷'),
                url: urls.testpaperDeleteUrl,
                progressRange: [7, 10]
            },
            {
                title: Translator.trans('删除课时资料'),
                url: urls.materialDeleteUrl,
                progressRange: [13, 16]
            },
            {
                title: Translator.trans('删除检查章节'),
                url: urls.chapterDeleteUrl,
                progressRange: [17, 20]
            },
            {
                title: Translator.trans('删除草稿'),
                url: urls.draftDeleteUrl,
                progressRange: [23, 26]
            },
            {
                title: Translator.trans('删除学习时长'),
                url: urls.lessonlearnsDeleteUrl,
                progressRange: [27, 30]
            },
            {
                title: Translator.trans('删除录播'),
                url: urls.lessonreplaysDeleteUrl,
                progressRange: [33, 34]
            },
            {
                title: Translator.trans('删除播放时长'),
                url: urls.lessonviewsDeleteUrl,
                progressRange: [35, 47]
            },
            {
                title: Translator.trans('删除作业'),
                url: urls.homeworkDeleteUrl,
                progressRange: [49, 51]
            },
            {
                title: Translator.trans('删除练习'),
                url: urls.exerciseDeleteUrl,
                progressRange: [52, 53]
            },
            {
                title: Translator.trans('删除课时'),
                url: urls.lessonDeleteUrl,
                progressRange: [55, 60]
            },
            {
                title: Translator.trans('删除课程收藏'),
                url: urls.favoriteDeleteUrl,
                progressRange: [63, 66]
            },
            {
                title: Translator.trans('删除课程笔记'),
                url: urls.noteDeleteUrl,
                progressRange: [67, 70]
            },
            {
                title: Translator.trans('删除话题'),
                url: urls.threadDeleteUrl,
                progressRange: [73, 76]
            },
            {
                title: Translator.trans('删除评价'),
                url: urls.reviewDeleteUrl,
                progressRange: [77, 80]
            },
            {
                title: Translator.trans('删除公告'),
                url: urls.announcementDeleteUrl,
                progressRange: [83, 86]
            },
            {
                title: Translator.trans('删除动态'),
                url: urls.statusDeleteUrl,
                progressRange: [87, 90]
            },
            {
                title: Translator.trans('删除成员'),
                url: urls.memberDeleteUrl,
                progressRange: [91, 93]
            },
            {
                title: Translator.trans('删除会话'),
                url: urls.conversationDeleteUrl,
                progressRange: [94, 97]
            },
            {
                title: Translator.trans('删除课程'),
                url: urls.courseDeleteUrl,
                progressRange: [98, 100]
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
                        $('.modal-title').text(Translator.trans('删除课程'));
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
                        $('#delete-form').find('.help-block').show().text(Translator.trans('验证密码错误'));
                    }
                });
            }
        });

        validator.addItem({
            element: '[name=password]',
            required: true,
            rule: 'minlength{min:5} maxlength{max:20}',
            display:Translator.trans('密码')
        });

        progressBar.on('completed', function() {
            progressBar.deactive();
            progressBar.text(Translator.trans('课程删除成功'));
            $("#delete-hint").hide();
            $("#finish-delete-btn").show();
            var courseId = $("input[name='courseId']").val();
            $('#course-tr-'+courseId+'').remove();
        });
	}

    function exec(title, url, progressBar, startProgress, endProgress) {

            progressBar.setProgress(startProgress, Translator.trans('正在%title%',{title:title}));
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
                    progressBar.setProgress(endProgress, Translator.trans('%title%完成',{title:title}));
                    $(document).dequeue('delete_step_queue');
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                progressBar.error( Translator.trans('%title%时，发生了未知错误。',{title:title}));
                $(document).clearQueue('delete_step_queue');
            });
    }
});
