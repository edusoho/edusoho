define(function(require, exports, module) {

    require("jquery.bootstrap-datetimepicker");
    var validator = require('bootstrap.validator');

    exports.run = function() {

        date();
        exportData();


        function exportData()
        {
            var $exportBtn = $('#export-btn');
            $exportBtn.on('click', function () {
                $exportBtn.button('loading');
                $.get($exportBtn.data('preUrl'), { start: 0 }, function (response) {
                   console.log(response);
                    if (response.status === 'getData') {
                        exportRecord(response.start, response.fileName);
                    } else {
                        $exportBtn.button('reset');
                        location.href = $exportBtn.data('url') + '?fileName=' + response.fileName;
                    }
                });
            });

            function exportRecord(start, fileName) {
                var start = start || 0,
                    fileName = fileName || '';

                $.get($exportBtn.data('preUrl'), { start: start, fileName: fileName }, function (response) {
                    if (response.status === 'getData') {
                        exportRecord(response.start, response.fileName);
                    } else {
                        $exportBtn.button('reset');
                        location.href = $exportBtn.data('url') + '&fileName=' + response.fileName;
                    }
                });
            }
        };

        function date ()
        {
            var $startDate = $("#startDate");
            var $endDate = $("#endDate");
            $startDate.datetimepicker({
                autoclose: true
            }).on('changeDate',function(){
                $endDate.datetimepicker('setStartDate',$startDate.val().substring(0,16));
            });

            $startDate.datetimepicker('setEndDate',$endDate.val().substring(0,16));
            $endDate.datetimepicker({
                autoclose: true
            }).on('changeDate',function(){
                $startDate.datetimepicker('setEndDate',$endDate.val().substring(0,16));
            });
            $endDate.datetimepicker('setStartDate', $startDate.val().substring(0,16));
        }


    };

});