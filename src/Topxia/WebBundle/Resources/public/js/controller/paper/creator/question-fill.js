define(function(require, exports, module) {

    var BaseQuestion = require('./question-base');
    var Validator = require('bootstrap.validator');

    var FillQuestion = BaseQuestion.extend({
        attrs: {
            stemEditorName: 'question'
        },
        setup: function() {
            FillQuestion.superclass.setup.call(this);

            this._initValidator();
        },

        _initValidator: function() {
            Validator.addRule('fillCheck',/(\[\[(.+?)\]\])/i, '请输入正确的答案,如今天是[[晴|阴|雨]]天.');
            this.get("validator").removeItem('#question-stem-field');
            this.get("validator").addItem({
                element: '#question-stem-field',
                required: true,
                rule:'fillCheck'
            });
        }
    });

    module.exports = FillQuestion;

});


