define(function(require, exports, module) {

    var player = new MediaPlayer({
        element: '#xxxx',
    });

    player.setSrc('xxxx');

    player.on('ended')
    player.on('error')
    player.play();

});