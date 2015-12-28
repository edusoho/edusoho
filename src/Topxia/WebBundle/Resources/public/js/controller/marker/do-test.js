define(function(require, exports, module) {
    exports.run = function() {
        // var close = $(".modal-header .close");
        var btn =$('#submitQuestion');
        var data = $("#data");
        var markerId = data.data('markerid');
        var questionId = data.data('questionid');
        var questionType = data.data('type');
        btn.on('click', function() {
        		var answer = doMarkerQuestion(questionType);
        		console.log(answer);
            $.get(data.data('url'), {
                "markerId": markerId,
                "questionId": questionId,
                "answer": answer,
                "type":questionType
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
        $("input[name='answer[" + questionId + "][]']").on("click", function(){
          if($(this).is(':checked')) {
              $(this).attr("checked", "checked");
          } else {
              $(this).removeAttr("checked");
          }
        });
        // close.on('click',function() {
        //     $(".modal").html("").hide();
        // });
        var doMarkerQuestion = function(type){
        	switch(type){
        		case "single_choice" :
        		return doSingleChoice();
        		break;
        		case "uncertain_choice" :
        		return doUncertainChoice();
        		break;
        		case "determine" :
        		return doDetermine();
        		break;
        		default:
        		break;
        	};
        	function doSingleChoice(){
        		var answer = null;
	        	answer = $("input[checked=checked]").val();
				    return answer;
	        };
	        function doUncertainChoice(){
	        	var answers = null;
	        	answers = $("input[checked=checked]").val();
	        	return answers;
	        }

        };
    }
});