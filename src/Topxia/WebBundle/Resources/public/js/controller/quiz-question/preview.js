define(function(require, exports, module) {

    var audioPlayers = require('./audio-player');

    $("#modal").on('hidden.bs.modal', function(){
        $.each(audioPlayers, function(i, audioPlayer) {
            audioPlayer.remove();
        });
    });

});