define(function(require, exports, module) {
  exports.run = function() {
    $('.lesson-export').on('click', function() {
      var prepare_url = $(this).data('prepare-url');
      var export_url = $(this).data('export-url');

      $('.lesson-export').button('loading');
      $.get(prepare_url, {start:0}, function(response) {
          if (response.status === 'getData') {
              exportCoursesLessons(response.start, response.fileName);
          } else {
              $('.lesson-export').button('reset');
              location.href = export_url +'&fileName='+response.fileName;
          }
          console.log(response);
      });

    });

    function exportCoursesLessons(start, fileName) {
        var start = start || 0,
            fileName = fileName || '';

        $.get($('.lesson-export').data('datasUrl'), {start:start, fileName:fileName}, function(response) {
            if (response.status === 'getData') {
                exportCoursesLessons(response.start, response.fileName);
            } else {
                $('.lesson-export').button('reset');
                location.href = $('.lesson-export').data('url')+'&fileName='+response.fileName;
            }
        });
    }


  };
});