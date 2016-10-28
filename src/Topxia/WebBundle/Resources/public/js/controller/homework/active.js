define(function(require, exports) {

	  exports.QuestionIndexActive = function(){
            $questionIds = $('.question-set-main').find('.question').each(function(index,item){

            $question = $(item);
            $choicesInputs = $question.find('.question-choices-inputs');
            $choicesInputs.each(function(index1,item1){
                $(item1).find('label > input').each(function(index2,item2){
                    if ($(item2).prop('checked')) {
                        $questionId = $question.data('questionId');
                        $('.question-set-card').find('.for-question-' + $questionId).addClass('question-index-active');
                    };
                });
            });

            $essayInputs = $question.find('.question-essay-input-short');
            if ($essayInputs.text()) {
                 $questionId = $question.data('questionId');
                    $('.question-set-card').find('.for-question-' + $questionId).addClass('question-index-active');
            };

            $fillInputs = $question.find('.question-fill-inputs');
            $fillInputs.each(function(index1,item1){
                $(item1).find('input').each(function(index2,item2){
                    if ($(item2).val()) {
                        $questionId = $question.data('questionId');
                        $('.question-set-card').find('.for-question-' + $questionId).addClass('question-index-active');
                    };
                });
            });
        });
	  }

});