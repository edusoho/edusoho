define(function(require, exports, module) {

    var BaseQuestion = require('./question-base');
    var EditorFactory = require('common/kindeditor-factory');
    var Uploader = require('upload');
    var Notify = require('common/bootstrap-notify');

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

            var editor = EditorFactory.create('#question-answer-field', 'simple_noimage');
            this.get('validator').on('formValidate', function(elemetn, event) {
                editor.sync();
            });

            var $trigger = this.$('[data-role=answer-uploader]');
            var uploader = new Uploader({
                trigger: $trigger,
                name: 'file',
                action: this.element.data('uploadUrl'),
                accept: 'image/*',
                error: function(file) {
                    Notify.danger('上传失败，请重试！')
                },
                success: function(response) {
                    Notify.success('上传成功！', 1);
                    var result = '[image]' + response.hashId + '[/image]'
                    editor.insertHtml(result);
                }
            });
        }
    });

    module.exports = EssayQuestion;

});


