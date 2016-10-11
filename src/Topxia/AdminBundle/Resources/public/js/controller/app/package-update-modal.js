define(function(require, exports, module) {

    var Widget = require('widget');

    exports.run = function() {
        $('#modal').on('hidden.bs.modal', function (e) {
            window.location.reload();
        })
        
        var progressBar = new ProgressBar({
            element: '#package-update-progress'
        });

        var $updateBtn = $("#begin-update");

        var urls = $updateBtn.data();
        var steps = getQueue(urls);

        $.each(steps, function(i, step) {
            $(document).queue('update_step_queue', function() {
                exec(step.title, step.url, progressBar, step.progressRange[0], step.progressRange[1]);
            });
        });

        progressBar.on('completed', function() {
            progressBar.deactive();
            progressBar.text(Translator.trans('应用安装/升级成功！'));
            $("#updating-hint").hide();
            $("#finish-update-btn").show();
        });


        $updateBtn.click(function() {

            if(!confirm(Translator.trans('是否确认升级，升级过程中网站将进入维护状态'))) {
                return;
            }

            $updateBtn.hide();
            $("#updating-hint").show();
            progressBar.show();

            $.post(urls.checkLastErrorUrl, function(result) {
                if (result === true) {
                    if(!confirm(Translator.trans('上次安装升级应用系统需回滚，继续安装可能会发生不可预料的错误，您确定继续吗？'))) {
                        $("#updating-hint").hide();
                        progressBar.hide();
                        $updateBtn.show();
                        return ;
                    }
                }
                $(document).dequeue('update_step_queue');
            }, 'json');

        });

        $("#finish-update-btn").click(function() {
            $(this).button('loading').addClass('disabled');
            setTimeout(function(){
                window.location.reload();
            }, 3000);
        });
    };


    function exec(title, url, progressBar, startProgress, endProgress) {
        progressBar.setProgress(startProgress, Translator.trans('正在%title%',{title:title}));
        $.ajax(url, {
            async: true,
            dataType: 'json',
            type: 'POST'
        }).done(function(data, textStatus, jqXHR) {
            if (data.status == 'error') {
                progressBar.error(makeErrorsText(Translator.trans('%title%失败：',{title:title}), data.errors));
            } else if (typeof(data.index) != "undefined") {
                if (url.indexOf('index') < 0) {
                    url = url+'&index=0';
                }
                url = url.replace(/index=\d+/g,'index='+data.index);
                endProgress = startProgress + data.progress;
                if (endProgress > 100) {
                    endProgress = 100;
                }
                progressBar.setProgress(endProgress, Translator.trans('%message%完成',{message:data.message}));
                startProgress = endProgress;
                title =  data.message;
                exec(title, url, progressBar, startProgress, endProgress);
            } else if(data.isUpgrade){
               $('.text-success').text(data.toVersion);
                var urls = $("#begin-update").data();
                steps = getQueue(urls);
                $.each(steps, function(i, step) {
                    var url = step.url.replace(/\d+/g,data.packageId);
                    $(document).queue('update_step_queue', function() {
                        exec(step.title, url, progressBar, step.progressRange[0], step.progressRange[1]);
                    });
                });
                $(document).dequeue('update_step_queue');
            } else {
                progressBar.setProgress(endProgress, Translator.trans('%title%完成',{title:title}));
                $(document).dequeue('update_step_queue');
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            if(title != '检查系统版本') {
                progressBar.error(Translator.trans('%title%时，发生了未知错误。',{title:title}));
                $(document).clearQueue('update_step_queue');
            } else {
                progressBar.setProgress(endProgress, title + Translator.trans('完成'));
                $(document).dequeue('update_step_queue');
            }
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

    var getQueue = function (urls){
        var steps = [
            {
                title: Translator.trans('检查系统环境'),
                url: urls.checkEnvironmentUrl,
                progressRange: [3, 10]
            },
            {
                title: Translator.trans('检查依赖'),
                url: urls.checkDependsUrl,
                progressRange: [13, 20]
            },
            {
                title: Translator.trans('备份系统文件'),
                url: urls.backupFileUrl,
                progressRange: [23, 30]
            },
            {
                title: Translator.trans('备份数据库'),
                url: urls.backupDbUrl,
                progressRange: [33, 40]
            },
            {
                title: Translator.trans('检查下载权限'),
                url: urls.checkDownloadExtractUrl,
                progressRange: [43, 50]
            },
            {
                title: Translator.trans('下载安装升级程序'),
                url: urls.downloadExtractUrl,
                progressRange: [53, 60]
            }
        ];


        var type = $("input[name='package-type']").val();
        if(type == 'upgrade'){
            var list = [
                {
                    title: Translator.trans('执行安装升级程序'),
                    url: urls.beginUpgradeUrl,
                    progressRange: [62, 94]
                },
                {
                    title: Translator.trans('检查系统版本'),
                    url: urls.checkNewestUrl,
                    progressRange: [97, 100]
                }
            ];

            $.merge(steps,list);
        }else{
            steps.push({
                title: Translator.trans('执行安装升级程序'),
                url: urls.beginUpgradeUrl,
                progressRange: [62, 100]
            });
        }

        return steps;
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