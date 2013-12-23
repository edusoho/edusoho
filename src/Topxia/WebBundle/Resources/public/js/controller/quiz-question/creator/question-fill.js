define(function(require, exports, module) {

    var BaseQuestion = require('./question-base');

    var FillQuestion = BaseQuestion.extend({
        setup: function() {
            FillQuestion.superclass.setup.call(this);
            this._initValidator();
        },

        _initValidator: function(){
            this.get("validator").removeItem('#question-stem-field');
            this.get("validator").addItem({
                element: '#question-stem-field',
                required: true,
                rule:'fillCheck',
            });
        },
    });

    module.exports = FillQuestion;

});


