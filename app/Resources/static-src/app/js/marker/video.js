let videoHtml = $('#task-dashboard');
let playerUrl = videoHtml.data('media-player');
let html = '<iframe src=\'' + playerUrl + '\' name=\'viewerIframe\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';
$('#task-video-content').html(html);