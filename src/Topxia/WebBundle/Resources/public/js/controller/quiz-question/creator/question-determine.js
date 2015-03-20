define(function(require, exports, module) {

    var BaseQuestion = require('./question-base');

    var DetermineQuestion = BaseQuestion.extend({
        setup: function() {
            DetermineQuestion.superclass.setup.call(this);
            this._initValidator();
        },

        _initValidator: function(){
            this.get("validator").addItem({
                element: '[name^=answer]',
                required: true,
                errormessage:"请选择您的答案"
            });
        }
    });

    module.exports = DetermineQuestion;

});


