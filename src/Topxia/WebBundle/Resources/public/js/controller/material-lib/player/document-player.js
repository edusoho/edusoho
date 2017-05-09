define(function (require, exports, module) {

  var DocumentPlayer = require('../../../../../topxiaweb/js/controller/widget/document-player');
  require('doc-player-new');

  exports.run = function () {
    var player = $("#document-player");
    var doc;
    $.get(player.data('url'), function (response) {
      if(response.html) {
        doc = new DocPlayerSDK({
          id: 'document-player',
          src: response.html,
          key: response.encryptKey,
          iv: response.iv
        });
      }else {
        var html = '<iframe id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\'></iframe>';
        player.html(html).show();
        doc = new DocumentPlayer({
          element: '#document-player',
          swfFileUrl: response.swf,
          pdfFileUrl: response.pdf
        });
      }
    }, 'json');
  }
});