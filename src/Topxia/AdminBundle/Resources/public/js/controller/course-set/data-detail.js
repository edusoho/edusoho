define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $taskExport = $('.task-export');

        $('#course-select').on('change',function(){
            var url = $(this).find("option:selected").data('url');
            $('#modal').load(url);
        })


        $taskExport.on('click', function() {
            var prepare_url = $(this).data('prepare-url');
            var export_url = $(this).data('export-url');

            $taskExport.button('loading');
            $.get(prepare_url, {start:0}, function(response) {
                if (response.error) {
                    Notify.danger(response.error);
                }
                if (response.method === 'getData') {
                   exportCoursesLessons(response.start, response.fileName);
                } else {
                   $taskExport.button('reset');
                   location.href = export_url +'?fileName=' + response.fileName;
                }
            });

        });

        function exportCoursesLessons(start, fileName) {
            var start = start || 0,
                fileName = fileName || '';

            $.get($taskExport.data('prepare-url'), {start:start, fileName:fileName}, function(response) {
                if (response.method === 'getData') {
                    exportCoursesLessons(response.start, response.fileName);
                } else {
                    $taskExport.button('reset');
                    location.href = $taskExport.data('export-url')+'?fileName='+response.fileName;
                }
            });
        }
    };
})