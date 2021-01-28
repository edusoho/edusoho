let videoHtml = $('#lesson-dashboard');
let playerUrl = videoHtml.data('media-player-url');
let html = '<iframe src=\'' + playerUrl + '\' name=\'viewerIframe\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';
$('#lesson-video-content').html(html);