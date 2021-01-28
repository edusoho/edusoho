define(function(require, exports, module) {

    var ProgressBar = require('../course/ProgressBar');

	exports.run = function() {

        var progressBar = new ProgressBar({
            element: '#optimize-files-progress'
        });

        progressBar.show();
        $(document).queue('optimize-files_queue', function() {
                exec(Translator.trans('admin.optimize.check_optimize_file'),$('#optimize-url').data('url'), progressBar, 20, 100);
        });
        $(document).dequeue('optimize-files_queue');

        progressBar.on('completed', function() {
            progressBar.deactive();
            progressBar.text(Translator.trans('admin.optimize.optimize_file_success_hint'));
            $("#optimize-hint").hide();
            $("#finish-optimize-btn").show();
        });
	}

    function exec(title, url, progressBar, startProgress, endProgress) {

            progressBar.setProgress(startProgress, Translator.trans('admin.optimize.optimizing_file_hint',{title:title}));
            $.ajax(url, {
                async: true,
                dataType: 'json',
                type: 'POST'
            }).done(function(data, textStatus, jqXHR) {
                if (data.status == 'error') {
                    progressBar.error(makeErrorsText(Translator.trans('admin.optimize.optimize_file_fail_message',{title:title}), data.errors));
                }else if(data.success){
                    progressBar.setProgress(startProgress, data.message);
                    title =  data.message;
                    exec(title, url, progressBar, startProgress, endProgress);
                }else{
                    progressBar.setProgress(endProgress,Translator.trans('admin.optimize.optimize_file_success_message',{title:title}));
                    $(document).dequeue('optimize-files_queue');
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                progressBar.error(Translator.trans('admin.optimize.optimize_file_error_message',{title:title}));
                $(document).clearQueue('optimize-files_queue');
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
});
