define(function(require, exports, module) {

    var AudioPlayer = require('./audioplayer');

    exports.run = function() {

        var players = [];

        var $triggers = $("#modal").find('.audio-play-trigger');

        $.each($triggers, function(i, trigger) {
            $(document).queue('audio_player', function() {
                var player = new AudioPlayer({
                    element: trigger
                });

                player.on('ended', function() {
                    $(document).dequeue('audio_player');
                });

                players.push(player);
            });
        });

        $("#modal").on('hidden.bs.modal', function() {
            $.each(players, function(i, player) {
                player.get('player').remove();
            });
        });

        $(document).dequeue('audio_player');

    }

});