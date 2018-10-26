define(function(require, exports, module) {

    var Widget = require('widget');

    exports.run = function() {
		var progressBar = new ProgressBar({
		    element: '#smsSend-progress'
		});
		var $sendBtn = $("#begin-smsSend");
        var url = $('#begin-smsSend').data('url');
        progressBar.on('completed', function() {
            progressBar.deactive();
            progressBar.text(Translator.trans('admin.sms.send_success_hint'));
            $("#finish-smsSend").show();
        });

        $sendBtn.click(function() {
        	$("#cancle-smsSend").hide();
            $sendBtn.hide();
            progressBar.show();
            exec(url, progressBar, 0, 0);
        });

         $("#finish-smsSend").click(function() {
            $(this).button('loading').addClass('disabled');
            setTimeout(function(){
                window.location.reload();
            });
        });

        var $ajaxUrl = $('#smsSend-form').data('url');
        if ($('input[name="sms_open"]').val()) {
            changeUrl();
        };
		function changeUrl () {
			$.get($ajaxUrl, function(response) {
				if (response.url) {
					url = url.replace(/url=(.*)+/g,'url='+response.url);
					$("#smsSend-ready").hide();
					$("#smsSend-form").show();
                    $("#cancle-smsSend").show();
                    $("#begin-smsSend").show();
				};
			});
			changeUrl=function(){};
		};
    };

    function exec(url, progressBar, startProgress, endProgress) {
        progressBar.setProgress(startProgress, Translator.trans('admin.sms.sending_hint'));
        $.ajax(url, {
            async: true,
            type: 'POST'
        }).done(function(data, textStatus, jqXHR) {
            if (data.status == 'error') {
                progressBar.error(makeErrorsText(title + Translator.trans('admin.sms.send_fail_hint'), data.errors));
            } else if (typeof(data.index) != "undefined") {
                url = url.replace(/index=\d+/g,'index='+data.index);
                endProgress = data.progress;
                progressBar.setProgress(endProgress, data.message+Translator.trans('admin.sms.finished'));
                startProgress = endProgress;
                exec(url, progressBar, startProgress, endProgress);
            } else if (data.status == 'success') {
            	endProgress = 100;
                progressBar.setProgress(endProgress, Translator.trans('admin.sms.send_finished'));
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            progressBar.error(Translator.trans('admin.sms.send_error_hint'));
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