define(function(require, exports, module) 

    var Widget = require('widget');

    exports.run = function() {
        var progressBar = new ProgressBar();

        $("#begin-update").click(function() {
            var urls = $(this).data();
            checkEnvironment(urls.checkEnvironmentUrl, progressBar);
        });

    };

    function checkEnvironment(url, progressBar) {
        progressBar.setProgress(5, '正在检查环境');
        $.ajax(url, {
            async: false,
            dataType: 'json',
            type: 'POST'
        }).done(function(data, textStatus, jqXHR) {
            progressBar.setProgress(5, '正在检查通过');
        }).fail(function(jqXHR, textStatus, errorThrown) {

        });
    }

    var ProgressBar = Widget.extend({
        attrs: {
            progress: 0,
            progressText: '',
        },

        setProgress: function(progress, text)
        {
            this.$('.progress-bar').css({width: progress + '%'});
            this.$('.progress-text').html(html);

            if (progress >= 100) {
                this.trigger('completed');
            }
        },

        reset: function() {
            this.$('.progress-bar').css({width: '0%'});
            this.$('.progress-text').html('');
        },

        show: function()
        {
            this.element.show();
        },

        hide: function()
        {
            this.element.hide();
        }

    }

});