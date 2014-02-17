define(function(require, exports, module) {

    var QuestionBase = require('./creator/question-base');
    var Choice = require('./creator/question-choice');
    var Determine = require('./creator/question-determine');
    var Essay = require('./creator/question-essay');
    var Fill = require('./creator/question-fill');

    exports.run = function() {
        var type = $('#question-creator-widget').find('[name=type]').val().replace(/\_/g, "-");
        var QuestionCreator;
        switch (type) {
            case 'single-choice':
            case 'uncertain-choice':
            case 'choice':
                QuestionCreator = Choice;
                break;
            case 'determine':
                QuestionCreator = Determine;
                break;
            case 'essay':
                QuestionCreator = Essay;
                break;
            case 'fill':
                QuestionCreator = Fill;
                break;
            default:
                QuestionCreator = QuestionBase;
        }

        var creator = new QuestionCreator({
            element: '#question-creator-widget'
        });

    };

});