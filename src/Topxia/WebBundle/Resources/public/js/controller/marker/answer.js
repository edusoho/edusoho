define(function(require, exports, module) {
    exports.run = function() {
    	var btn =$('#continue');
        var data = $("#data");
        var markerId = data.data('markerid');
        var questionId = data.data('questionid');
        var questionType = data.data('type');
        btn.on('click', function() {
            var player = window.frames["viewerIframe"].window.BalloonPlayer;
            player.trigger('onMarkerReached', markerId);
        });
        $(".marker-modal .close").on('click', function() {
            console.log($(this).closest('#modal'));
            $(this).closest('#modal').hide();
        });
    }
});