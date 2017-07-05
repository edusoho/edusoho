define(function(require, exports, module) {

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

                exportData(0, null, urls);
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
                if (response.status === 'getData') {
                    exportData(response.start, response.fileName, urls);
                } else {
                    $exportBtn.button('reset');
                    location.href = urls.url + '?fileName=' + response.fileName;
                }
            });
        }

    };

});