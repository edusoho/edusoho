define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var $exportBtn = $('#export-btn');
        var $modal = $('#modal');
        exportDataEvent();

        function exportDataEvent()
        {
            $exportBtn.on('click', function () {
                var $form = $($exportBtn.data('targetForm'));
                var formData = $form.length > 0 ? $form.serialize() : '';
                var preUrl = $exportBtn.data('preUrl') + '?' + formData;
                $exportBtn.button('loading');

                var urls = {'preUrl':preUrl, 'url':$exportBtn.data('url')};

                exportData(0, '', urls);
            });
        };

        function exportData(start, filePath, urls) {
            if (0 == start) {
                showProgress();
            }
            var data = {
                'start': start
            }

            if (filePath != '') {
                data.filePath = filePath;
            }

            $.get(urls.preUrl, data, function (response) {
                if (response.error) {
                    Notify.danger(response.error);
                    return;
                }
                if (response.status === 'getData') {
                    var process = response.start * 100 / response.count + '%';
                    $modal.find('#progress-bar').width(process);
                    exportData(response.start, response.filePath, urls);
                } else {
                    $exportBtn.button('reset');
                    finish();
                    location.href = urls.url + '?filePath=' + response.filePath;
                }
            });
        }

        function finish() {
            $modal.find('#progress-bar').width('100%');
            $modal.find('.title').text('下载成功');
            $modal.modal('hide');
        }

        function showProgress() {
            var progressHtml = $('#export-modal').html();
            $modal.html(progressHtml);
            $modal.modal();
        }
    };

});