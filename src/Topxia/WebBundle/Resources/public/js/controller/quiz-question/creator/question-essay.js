define(function(require, exports, module) {

    var BaseQuestion = require('./question-base');
    var Uploader = require('upload');
    var Notify = require('common/bootstrap-notify');
    // require('webuploader');
    require('es-ckeditor');

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

            // group: 'default'
            var editor = CKEDITOR.replace('question-answer-field', {
                toolbar: 'Minimal',
                filebrowserImageUploadUrl: $('#question-answer-field').data('imageUploadUrl'),
                height: 120
            });

            this.get('validator').on('formValidate', function(elemetn, event) {
                editor.updateElement();
            });

            var $trigger = this.$('[data-role=answer-uploader]');
        }
    });

    module.exports = EssayQuestion;

});


