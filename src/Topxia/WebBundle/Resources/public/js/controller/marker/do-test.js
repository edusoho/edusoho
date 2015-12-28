define(function(require, exports, module) {
    exports.run = function() {
        var close = $(".modal-header .close");
        var btn =$('#submitQuestion');
        var data = $("#data");
        var markerId = data.data('markerid');
        var questionId = data.data('questionid');
        console.log(markerId);
        btn.on('click', function() {
        	console.log(markerId);
            $.get(data.data('url'), {
                "markerId": markerId,
                "questionId": questionId
            }, function(data) {
            	console.log(markerId);
                var player = window.frames["viewerIframe"].window.BalloonPlayer;
                player.trigger('doNextQuestionMarker', data);
            });
        });
        $("input[name='answer[" + questionId + "]']").on('click', function() {
            if ($(this).is(':checked')) {
                $(this).attr("checked", "checked").parent().siblings().find("input").removeAttr("checked");
            }
        });
        close.on('click',function() {
            $(".modal").html("").hide();
        });
    }
});