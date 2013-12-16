define(function(require, exports, module) {
	
    var Choice =  require('./creator/question-choice');
    var Determine =  require('./creator/question-determine');
    var Essay =  require('./creator/question-essay');
    var Materialhoice =  require('./creator/question-material');
    var Fill =  require('./creator/question-fill');

    exports.run = function() {
    	var type = $('#question-creator-widget').find('[name=type]').val().replace(/\_/g,"-");
	    switch(type){
		  case 'single-choice':
		  case 'choice':
			var QuestionCreator = Choice;break;
		  case 'determine':
			var QuestionCreator = Determine;break;
		  case 'essay':
			var QuestionCreator = Essay;break;
		  case 'material':
			var QuestionCreator = Materialhoice;break;
		  case 'fill':
			var QuestionCreator = Fill;break;
		}
		
        var creator = new QuestionCreator({
            element: '#question-creator-widget',
            form: '#question-create-form',
        });
    };

});