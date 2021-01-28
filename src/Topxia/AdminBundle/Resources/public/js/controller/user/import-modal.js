define(function(require, exports, module) {

    var Widget = require('widget');

    exports.run = function() {
        
        var progressBar = new ProgressBar({
            element: '#user-import-progress'
        });

        var $updateBtn = $("#begin-update");
        var importUrl = $("#user-import").data('import-url');
        var finishedUrl = $("#user-import").data('finished-url');
        var checkType = $("#user-import").data('check-type');

        var datas = $.parseJSON($("input[name='data']").val());
        var progress = $.parseJSON($("input[name='progress']").val());

        var steps = new Array();
        var progressNum = 0;

        $.each(datas, function(i, item) {
            steps.push({
                 title: Translator.trans('admin.user.import_progress_hint',{progressNum:progressNum}),
                 url: importUrl,
                 data:{'data': JSON.stringify(item),'checkType':checkType},
                 progressRange: [i*100/progress.length, (i+1)*100/progress.length]
            });
            progressNum += progress[i];
        });

        $.each(steps, function(i, step) {
            $(document).queue('update_step_queue', function() {
                exec(step.title, step.url, step.data, progressBar, step.progressRange[0], step.progressRange[1]);
            });
        });

        progressBar.on('completed', function() {
            progressBar.deactive();
            progressBar.text(Translator.trans('admin.user.import_finish_hint'));
            $("#updating-hint").hide();
            $("#finish-import-btn").show();
        });

        $updateBtn.hide();
        $("#updating-hint").show();
        progressBar.show();
        $(document).dequeue('update_step_queue');

        $("#finish-import-btn").click(function() {
            $(this).button('loading').addClass('disabled');
            setTimeout(function(){
                window.location.href = finishedUrl;
            }, 1000);
        });
    };

    function exec(title, url, data, progressBar, startProgress, endProgress) {
        progressBar.setProgress(startProgress, title);
        $.ajax(url, {
            async: true,
            dataType: 'json',
            data: data,
            type: 'POST'
        }).done(function(data, textStatus, jqXHR) {
            if (data.status == 'error') {
                progressBar.error(makeErrorsText(Translator.trans('admin.user.import_fail_hint',{title:title}), data.errors));
            } else if (typeof(data.index) != "undefined") {
                if (url.indexOf('index') < 0) {
                    url = url+'&index=0';
                }
                url = url.replace(/index=\d+/g,'index='+data.index);
                endProgress = startProgress + data.progress;
                if (endProgress > 100) {
                    endProgress = 100;
                }
                progressBar.setProgress(endProgress, Translator.trans('admin.user.import_finish_hint',{title:data.message}));
                startProgress = endProgress;
                title =  data.message;
                exec(title, url, progressBar, startProgress, endProgress);
            } else {
                progressBar.setProgress(endProgress, Translator.trans('admin.user.import_finish_hint',{title:title}));
                $(document).dequeue('update_step_queue');
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            progressBar.error(Translator.trans('admin.user.import_error_hint',{title:title}));
            $(document).clearQueue('update_step_queue');
        });
    }

    function makeErrorsText(title, errors) {
        var html = '<p>' + title + '<p>';
        html += '<ul>';
        $.each(errors, function(index, text) {
            html += '<li>' + text + '</li>';
        });
        html += '</ul>';
        return html;
    }

    var ProgressBar = Widget.extend({

        setProgress: function(progress, text) {
            this.$('.progress-bar').css({width: progress + '%'});
            this.$('.progress-text').html(text);

            if (progress >= 100) {
                this.trigger('completed');
            }
        },

        reset: function() {
            this.$('.progress-bar').css({width: '0%'});
            this.$('.progress-text').html('');
        },

        show: function() {
            this.element.show();
        },

        hide: function() {
            this.element.hide();
        },

        active: function() {
            this.$('.progress').addClass('active');
        },

        deactive: function() {
            this.$('.progress').removeClass('active');
        },

        text: function(text) {
            this.$('.progress-text').html(text);
        },

        error: function(text) {
            this.$('.progress-bar').addClass('progress-bar-danger');
            this.$('.progress-text').addClass('text-danger').html(text);
            this.deactive();
        },

        recovery: function() {
            this.$('.progress-bar').removeClass('progress-bar-danger');
            this.$('.progress-text').removeClass('text-danger').html('');
            this.active();
        },

        hasError: function() {
            return this.$('.progress-bar').hasClass('progress-bar-danger');
        }

    });

});