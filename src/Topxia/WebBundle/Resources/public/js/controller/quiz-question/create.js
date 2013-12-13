define(function(require, exports, module) {
    
    exports.run = function() {
    	var type = $('#question-creator-widget').find('[name=type]').val().replace(/\_/g,"-");
	    switch(type){
		  case 'single-choice':
		  case 'choice':
			var QuestionCreator = require('./creator/question-choice');break;
		  case 'determine':
			var QuestionCreator = require('./creator/question-determine');break;
		  case 'essay':
			var QuestionCreator = require('./creator/question-essay');break;
		  case 'material':
			var QuestionCreator = require('./creator/question-material');break;
		  case 'fill':
			var QuestionCreator = require('./creator/question-fill');break;
		}
		
        var creator = new QuestionCreator({
            element: '#question-creator-widget',
            form: '#question-create-form',
        });

        

    };

});