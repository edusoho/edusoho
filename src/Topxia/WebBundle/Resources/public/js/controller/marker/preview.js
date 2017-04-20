define(function (require, exports, module) {

  var Messenger = require('../player/messenger');
  var videoHtml = $('#task-dashboard');
  var playerUrl = videoHtml.data("media-player");

  var html = '<iframe src=\'' + playerUrl + '\' name=\'viewerIframe\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';
 console.log(html)
  $("#task-video-content").html(html);

});
