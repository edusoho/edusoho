define(function(require, exports, module) {

    exports.run = function() {
        var $exportBtn = $('#export-btn');

        exportData();

        function exportData()
        {
            $exportBtn.on('click', function () {
                var $form = $($exportBtn.data('targetForm'));
                var formData = $form.length > 0 ? $form.serialize() : '';
                var preUrl = $exportBtn.data('preUrl') + '?' + formData;

                $exportBtn.button('loading');
                $.get(preUrl, {start: 0}, function (response) {
                    console.log(response);
                    if (response.status === 'getData') {
                        exportRecord(response.start, response.fileName, preUrl);
                    } else {
                        $exportBtn.button('reset');
                        location.href = $exportBtn.data('url') + '?fileName=' + response.fileName;
                    }
                });
            });
        };

        function exportRecord(start, fileName, preUrl) {
            var start = start || 0,
                fileName = fileName || '';
            $.get(preUrl, {start:start,fileName:fileName}, function (response) {
                if (response.status === 'getData') {
                    exportRecord(response.start, response.fileName, data);
                } else {
                    $exportBtn.button('reset');
                    location.href = $exportBtn.data('url') + '&fileName=' + response.fileName;
                }
            });
        }

    };

});