define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var $exportBtn = $('#export-btn');

        exportDataEvent();

        function exportDataEvent()
        {
            $exportBtn.on('click', function () {
                var $form = $($exportBtn.data('targetForm'));
                var formData = $form.length > 0 ? $form.serialize() : '';
                var preUrl = $exportBtn.data('preUrl') + '?' + formData;
                $exportBtn.button('loading');

                var urls = {'preUrl':preUrl, 'url':$exportBtn.data('url')};

                exportData(0, false, urls);
            });
        };

        function exportData(start, fileName, urls) {
            var data = {
                'start': start
            }
            if (fileName) {
                data.fileName = fileName;
            }
            $.get(urls.preUrl, data, function (response) {
                if (response.error) {
                    Notify.danger(response.error);
                }
                if (response.status === 'getData') {
                    exportData(response.start, response.filePath, urls);
                } else {
                    $exportBtn.button('reset');
                    location.href = urls.url + '?filePath=' + response.filePath;
                }
            });
        }

    };

});