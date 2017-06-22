define(function (require, exports, module) {

  var DocumentPlayer = require('../../../../../topxiaweb/js/controller/widget/document-player');
  require('doc-player-new');

  exports.run = function () {
    var player = $("#document-player");
    var html = '<iframe id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\'></iframe>';
    player.html(html).show();

    var doc;
    $.get(player.data('url'), function (response) {
      var player = new DocumentPlayer({
          element: '#document-player',
          swfFileUrl: response.swf,
          pdfFileUrl: response.pdf
      });
    }, 'json');
  }
});