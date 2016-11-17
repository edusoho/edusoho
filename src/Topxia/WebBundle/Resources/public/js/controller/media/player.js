define(function (require, exports, module) {
    var Messenger = require('../player/messenger');

    var $elem = $('#lesson-dashboard');
    var playerUrl = $elem.data('mediaPlayerUrl');
    var html = '<iframe src=\'' + playerUrl + '\' name=\'viewerIframe\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';
    $("#lesson-video-content").html(html);
    
    module.exports = new Messenger({
        name: 'parent',
        project: 'PlayerProject',
        children: [document.getElementById('viewerIframe')],
        type: 'parent'
    });

});
