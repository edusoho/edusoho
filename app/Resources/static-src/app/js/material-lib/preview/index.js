const playerDiv = $('#material-preview-player');
const url = playerDiv.data('url');

if (playerDiv.length > 0) {
  const html = '<iframe src=\'' + url + '\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';
	
  playerDiv.html(html);
}

const $modal = $('#modal');
$modal.on('hidden.bs.modal', function() {
  if (playerDiv.length > 0) {
    playerDiv.html('');
  }
});