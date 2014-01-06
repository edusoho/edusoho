define(function(require, exports, module) {

    var Test = {
    	MenuTotal : function(){
			var total = 0;
			var questionTotal = 0;
			var questionType = $('#myTab .active a').text();
			var questionConut = $('[name^=scores]:visible').length;

			$('[name^=scores][type=text]').each(function(){
			    total = Number($(this).val()) + Number(total);
			});

			$('[name^=scores]:visible').each(function(){
			    questionTotal = Number($(this).val()) + Number(questionTotal);
			});

			if(isNaN(total) || isNaN(questionTotal)){
				total = 0;
				questionTotal = 0;
			}
			
			var html = "试卷总分" + total + "分 " + questionType + questionConut + "题/ "+ questionTotal + "分";

			$('.score-text-alert').html(html);
    	}
    };

    module.exports = Test;

});