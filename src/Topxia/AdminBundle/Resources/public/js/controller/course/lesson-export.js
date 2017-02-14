define(function(require, exports, module) {
  exports.run = function() {
    $('.lesson-export').on('click', function() {
      var prepare_url = $(this).data('prepare-url');
      var export_url = $(this).data('export-url');

      $('.lesson-export').button('loading');
      $.get(prepare_url, {start:0}, function(response) {
          if (response.method === 'getData') {
              exportCoursesLessons(response.start, response.fileName);
          } else {
              $('.lesson-export').button('reset');
              location.href = $('.lesson-export').data('export-url') +'?fileName='+response.fileName;
          }
      });

    });

    function exportCoursesLessons(start, fileName) {
        var start = start || 0,
            fileName = fileName || '';

        $.get($('.lesson-export').data('prepare-url'), {start:start, fileName:fileName}, function(response) {
            if (response.method === 'getData') {
                exportCoursesLessons(response.start, response.fileName);
            } else {
                $('.lesson-export').button('reset');
                location.href = $('.lesson-export').data('export-url')+'?fileName='+response.fileName;
            }
        });
    }


  };
});