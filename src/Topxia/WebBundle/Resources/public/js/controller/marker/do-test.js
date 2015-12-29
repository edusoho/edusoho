define(function(require, exports, module) {
    exports.run = function() {
        var btn =$('#submitQuestion');
        var data = $("#data");
        var markerId = data.data('markerid');
        var questionId = data.data('questionid');
        var questionType = data.data('type');
        btn.on('click', function() {
        		var answer = doMarkerQuestion(questionType);
            $.get(data.data('url'), {
                "markerId": markerId,
                "questionId": questionId,
                "answer": answer,
                "type":questionType
            }, function(data) {
                var player = window.frames["viewerIframe"].window.BalloonPlayer;
                player.trigger('onMarkerReached', data);
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
	        	var answers = [];
	        	$("input[name='answer[" + questionId + "][]']:checked").each(function(){ 
							answers.push($(this).val()); 
						});
	        	return answers;
	        };
	        function doDetermine(){
        		var answer = null;
	        	answer = $("input[checked=checked]").val();
				    return answer;
	        };
        };
    }
});