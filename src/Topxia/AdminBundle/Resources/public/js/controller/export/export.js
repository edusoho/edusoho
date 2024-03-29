define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var $exportBtns = $('.js-export-btn');
        var $exportBtn;
        var exportFileNames = [];
        var totalCount = 0;
        var currentCount = 0;

        var $modal = $('#modal');
        exportDataEvent();

        function exportDataEvent()
        {
            $exportBtns.on('click', function () {
                $exportBtn = $(this);

                var $form = $($exportBtn.data('targetForm'));
                var formData = $form.length > 0 ? $form.serialize() : '';
                var preUrl = $exportBtn.data('preUrl') + '?' + formData;
                var tryUrl = $exportBtn.data('tryUrl') + '?' + formData;
                var can = tryExport(tryUrl);
                if (!can) {
                    return false;
                }

                $exportBtn.button('loading');
                var urls = {'preUrl':preUrl, 'url':$exportBtn.data('url')};
                showProgress();

                exportData(0, '', urls, '');
            });
        };

        function tryExport(tryUrl)
        {
            var can = true;
            $.ajax({
                type : "get",
                url : tryUrl,
                async : false,
                data: {
                  names: $exportBtn.data('fileNames')
                },
                success : function(response){
                    if (!response.success) {
                        notifyError(Translator.trans(response.message,response.parameters));
                        can = false;
                    } else {
                      response.counts.forEach(function(val) {
                        totalCount += val;
                      }, 0);
                    }
                }
            });

            return can;
        }

        function exportData(start, fileName, urls, currentName) {
            var data = {
                'start': start,
                'fileName': fileName,
                'names': $exportBtn.data('fileNames'),
                'name': currentName,
            }

            $.get(urls.preUrl, data, function (response) {
                if (!response.success) {
                    Notify.danger(Translator.trans(response.message));
                    return;
                }

                if (response.name !== '') {
                    if (response.status === 'finish') {
                      exportFileNames.push(response.csvName);
                      currentCount += response.count;
                    }
                    var process = (response.start + currentCount) * 100 / totalCount + '%';
                    console.log(process);
                    $modal.find('#progress-bar').width(process);
                    exportData(response.start, response.fileName, urls, response.name);
                } else {
                  exportFileNames.push(response.csvName);
                    $exportBtn.button('reset');
                    download(urls, exportFileNames) ?  finish() : notifyError('unexpected error, try again');
                }
            }).error(function(e){
                console.log(e);
                Notify.danger(Translator.trans(e.responseJSON.error.message));
            });
        }

        function finish() {
            $modal.find('#progress-bar').width('100%').parent().removeClass('active');
            var $title = $modal.find('.modal-title');
            setTimeout(function(){
                Notify.success($title.data('success'));
                $modal.modal('hide');
            },500);

        }

        function showProgress() {
            var progressHtml = $('#export-modal').html();
            $modal.html(progressHtml);
            $modal.modal({backdrop: 'static', keyboard: false});
        }

        function download(urls, fileNames) {
            if (urls.url && fileNames) {
                var url = urls.url + '&';
                $.each(fileNames, function (index, value) {
                  url += 'fileNames[]=' + value + '&';
                });
                totalCount = 0;
                currentCount = 0;
                exportFileNames = [];
                window.location.href = url;
                return true;
            }

            return false;
        }

        function notifyError(message){
            $modal.modal('hide');
            Notify.danger(message);
        }
    };

});