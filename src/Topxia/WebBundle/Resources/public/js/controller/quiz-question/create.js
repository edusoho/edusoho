define(function(require, exports, module) {

    var QuestionCreator = require('./question-creator');

    exports.run = function() {

        var creator = new QuestionCreator({
            element: '#question-creator-widget',
            index: 4
        });


    };

});