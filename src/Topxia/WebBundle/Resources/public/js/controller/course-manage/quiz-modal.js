define(function(require, exports, module) {

    var QuizModalWiget = require('./quiz-modal-wiget');

    exports.run = function() {
        
        var quizModalWiget = new QuizModalWiget({
            element: '#quiz-manager'
        }).render();

    };

});