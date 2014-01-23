define(function(require, exports, module) {

    var BaseQuestion = require('./question-base');

    var MaterialQuestion = BaseQuestion.extend({
        setup: function() {
            MaterialQuestion.superclass.setup.call(this);
        }
    });
    module.exports = MaterialQuestion;

});


