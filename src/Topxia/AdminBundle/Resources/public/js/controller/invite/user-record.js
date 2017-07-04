define(function(require, exports, module) {

    exports.run = function() {
        exportData();

        function exportData()
        {
            var $exportBtn = $('#export-btn');
            $exportBtn.on('click', function () {
                $exportBtn.button('loading');
                var data = {
                    'nickname': $('#nickname').val(),
                }
                data.start = 0;
                $.get($exportBtn.data('preUrl'), data, function (response) {
                    console.log(response);
                    if (response.status === 'getData') {
                        exportRecord(response.start, response.fileName, data);
                    } else {
                        $exportBtn.button('reset');
                        location.href = $exportBtn.data('url') + '?fileName=' + response.fileName;
                    }
                });
            });

            function exportRecord(start, fileName, data) {
                var start = start || 0,
                    fileName = fileName || '';
                data.start = start;
                data.fileName = fileName;

                $.get($exportBtn.data('preUrl'), data, function (response) {
                    if (response.status === 'getData') {
                        exportRecord(response.start, response.fileName, data);
                    } else {
                        $exportBtn.button('reset');
                        location.href = $exportBtn.data('url') + '&fileName=' + response.fileName;
                    }
                });
            }
        };

    };

});