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
                title: '检查问题删除',
                url: urls.questionDeleteUrl,
                progressRange: [3, 6]
            },
            {
                title: '检查试卷删除',
                url: urls.testpaperDeleteUrl,
                progressRange: [7, 10]
            },
            {
                title: '检查课时资料删除',
                url: urls.materialDeleteUrl,
                progressRange: [13, 16]
            },
            {
                title: '检查章节删除',
                url: urls.chapterDeleteUrl,
                progressRange: [17, 20]
            },
            {
                title: '检查草稿删除',
                url: urls.draftDeleteUrl,
                progressRange: [23, 26]
            },
            {
                title: '检查学习时长删除',
                url: urls.lessonlearnsDeleteUrl,
                progressRange: [27, 30]
            },
            {
                title: '检查录播删除',
                url: urls.lessonreplaysDeleteUrl,
                progressRange: [33, 36]
            },
            {
                title: '检查播放时长删除',
                url: urls.lessonviewsDeleteUrl,
                progressRange: [37, 40]
            },
            {
                title: '检查作业删除',
                url: urls.homeworkDeleteUrl,
                progressRange: [53, 56]
            },
            {
                title: '检查练习删除',
                url: urls.exerciseDeleteUrl,
                progressRange: [57, 60]
            },
            {
                title: '检查课时删除',
                url: urls.lessonDeleteUrl,
                progressRange: [40, 50]
            },
            {
                title: '检查课程收藏删除',
                url: urls.favoriteDeleteUrl,
                progressRange: [63, 66]
            },
            {
                title: '检查课程笔记删除',
                url: urls.noteDeleteUrl,
                progressRange: [67, 70]
            },
            {
                title: '检查话题删除',
                url: urls.threadDeleteUrl,
                progressRange: [73, 76]
            },
            {
                title: '检查评价删除',
                url: urls.reviewDeleteUrl,
                progressRange: [77, 80]
            },
            {
                title: '检查公告删除',
                url: urls.announcementDeleteUrl,
                progressRange: [83, 86]
            },
            {
                title: '检查动态删除',
                url: urls.statusDeleteUrl,
                progressRange: [87, 96]
            },
            {
                title: '检查成员删除',
                url: urls.memberDeleteUrl,
                progressRange: [97, 98]
            },
            {
                title: '检查课程删除',
                url: urls.courseDeleteUrl,
                progressRange: [99, 100]
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
                if (data.status == 'error') {
                    progressBar.error(makeErrorsText(title + '失败：', data.errors));
                }else if(data.success){
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
