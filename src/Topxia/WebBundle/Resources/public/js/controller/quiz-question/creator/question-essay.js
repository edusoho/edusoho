define(function(require, exports, module) {

    var BaseQuestion = require('./question-base');

    var EssayQuestion = BaseQuestion.extend({
        setup: function() {
            EssayQuestion.superclass.setup.call(this);
            this._initValidator();
        },

        _initValidator: function(){
            this.get("validator").addItem({
                element: '#question-answer-field',
                required: true
            });
        },
    });

    module.exports = EssayQuestion;

});


