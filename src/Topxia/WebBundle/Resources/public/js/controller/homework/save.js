define(function(require, exports) {

	var saveAnswers = {};

	exports.save = function ($btn,Redirect) {
		var $homeworkFinishBtn = $('#homework-finish-btn');

		$homeworkFinishBtn.text('正在保存结果...');
		$homeworkFinishBtn.attr('disabled',true);

		$('.question-set-main').find('.question').each(function(index,item){

			$question = $(item);

			$choicesInputs = $question.find('.question-choices-inputs');
			$choicesInputs.each(function(index1,item1){
	            var answer = [];
			    $(item1).find('label > input').each(function(index2,item2){
			        if ($(item2).prop('checked')) {
			            $questionId = $question.data('questionId');
			            answer.push($(item2).val());
			        } else {
			            
			            $questionId = $question.data('questionId');
			        }
			    });
			    saveAnswers[$questionId] = answer;
			});

			$essayInputs = $question.find('[data-type="essay"]');
			var answer = [];
			$essayInputs.each(function(index1,item1){
		        $questionId = $question.data('questionId');
		        if ($(item1).val() != '') {
		        	answer.push($(item1).val());
		        	saveAnswers[$questionId] = answer;
		        }
			});

			$fillInputs = $question.find('.question-fill-inputs');
			$fillInputs.each(function(index1,item1){
			    var answer = [];
			    $(item1).find('input').each(function(index2,item2){
			        answer.push($(item2).val())
			        $questionId = $question.data('questionId');
			    });
			    saveAnswers[$questionId] = answer;
			});
		});
	    $.post($btn.data('url'),{data:saveAnswers},function(res){
			saveAnswers = {};
			var returnUrl = $btn.data('returnUrl');
			location.href = returnUrl;
	    });
  };

});