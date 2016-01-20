define(function(require, exports, module) {
    exports.run = function() {
    	var btn =$('#continue');
        var data = $("#data");
        var markerId = data.data('markerid');
        var questionId = data.data('questionid');
        var questionType = data.data('type');
        btn.on('click', function() {
            var player = window.frames["viewerIframe"].window.BalloonPlayer;
            player.trigger('onMarkerReached', markerId,questionId);
        });
        $(".marker-modal .close").on('click', function() {
            $(this).closest('#modal').hide();
        });
    }
});