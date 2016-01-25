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
                title: '删除问题',
                url: urls.questionDeleteUrl,
                progressRange: [3, 6]
            },
            {
                title: '删除试卷',
                url: urls.testpaperDeleteUrl,
                progressRange: [7, 10]
            },
            {
                title: '删除课时资料',
                url: urls.materialDeleteUrl,
                progressRange: [13, 16]
            },
            {
                title: '删除检查章节',
                url: urls.chapterDeleteUrl,
                progressRange: [17, 20]
            },
            {
                title: '删除草稿',
                url: urls.draftDeleteUrl,
                progressRange: [23, 26]
            },
            {
                title: '删除学习时长',
                url: urls.lessonlearnsDeleteUrl,
                progressRange: [27, 30]
            },
            {
                title: '删除录播',
                url: urls.lessonreplaysDeleteUrl,
                progressRange: [33, 34]
            },
            {
                title: '删除播放时长',
                url: urls.lessonviewsDeleteUrl,
                progressRange: [35, 47]
            },
            {
                title: '删除作业',
                url: urls.homeworkDeleteUrl,
                progressRange: [49, 51]
            },
            {
                title: '删除练习',
                url: urls.exerciseDeleteUrl,
                progressRange: [52, 53]
            },
            {
                title: '删除课时',
                url: urls.lessonDeleteUrl,
                progressRange: [55, 60]
            },
            {
                title: '删除课程收藏',
                url: urls.favoriteDeleteUrl,
                progressRange: [63, 66]
            },
            {
                title: '删除课程笔记',
                url: urls.noteDeleteUrl,
                progressRange: [67, 70]
            },
            {
                title: '删除话题',
                url: urls.threadDeleteUrl,
                progressRange: [73, 76]
            },
            {
                title: '删除评价',
                url: urls.reviewDeleteUrl,
                progressRange: [77, 80]
            },
            {
                title: '删除公告',
                url: urls.announcementDeleteUrl,
                progressRange: [83, 86]
            },
            {
                title: '删除动态',
                url: urls.statusDeleteUrl,
                progressRange: [87, 96]
            },
            {
                title: '删除成员',
                url: urls.memberDeleteUrl,
                progressRange: [97, 98]
            },
            {
                title: '删除课程',
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
