import notify from 'common/notify';

let $exportBtn = $('#export-students-transcript');

$exportBtn.on('click', function () {
  $exportBtn.button('loading');
  exportTranscript();
});

function exportTranscript(start, fileName) {
  start = start || 0;
  let query = fileName ? {start: start, fileName: fileName} : {start: start};

  $.get($exportBtn.data('dataUrl'), query, function (response) {
    if (response.status === 'getData') {
      exportTranscript(response.start, response.fileName);
    } else {
      $exportBtn.button('reset');
      location.href = $exportBtn.data('url') + '?fileName=' + response.fileName;
    }
  });
}