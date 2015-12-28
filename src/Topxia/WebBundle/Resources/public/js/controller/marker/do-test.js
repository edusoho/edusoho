define(function(require, exports, module) {
    exports.run = function() {
        // var close = $(".modal-header .close");
        var btn =$('#submitQuestion');
        var data = $("#data");
        var markerId = data.data('markerid');
        var questionId = data.data('questionid');
        btn.on('click', function() {
        	var status = "none";
			    	var answers = data.data('answer');
				    for(var i=0;i<4;i++) {
				    	if(answers[i].checked) {
								if(answers[i].value == answer)	{
				    			status = "right";
				    		} else {
				    			status = "wrong"
				    		}
				    	}
				    }
            $.get(data.data('url'), {
                "markerId": markerId,
                "questionId": questionId
                "status": status
            }, function(data) {
                var player = window.frames["viewerIframe"].window.BalloonPlayer;
                player.trigger('doNextQuestionMarker', data);
            });
        });
        $("input[name='answer[" + questionId + "]']").on('click', function() {
            if ($(this).is(':checked')) {
                $(this).attr("checked", "checked").parent().siblings().find("input").removeAttr("checked");
            }
        });
        // close.on('click',function() {
        //     $(".modal").html("").hide();
        // });
    }
});