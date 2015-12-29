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
        $(".marker-modal .question-single_choice li").on('click', function() {
            var $this = $(this).find('input');
            if (!$this.is(':checked')) {
                $this.prop("checked", true);
                $this.attr("checked", "checked");
                $(this).siblings().find("input").removeAttr("checked");
            }
        });
        $(".marker-modal .question-uncertain_choice li").on("click", function(){
            var $this = $(this).find('input');
            if(!$this.is(':checked')) {
                $this.prop("checked", true);
                $this.attr("checked", "checked");
            } else {
                $this.removeAttr("checked");
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