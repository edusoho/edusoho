define(function(require, exports, module) {

    var BaseQuestion = require('./question-base');
    var Uploader = require('upload');
    var Notify = require('common/bootstrap-notify');
    require('webuploader');
    require('ckeditor');

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
                height: 120
            });

            this.get('validator').on('formValidate', function(elemetn, event) {
                editor.updateElement();
            });

            var $trigger = this.$('[data-role=answer-uploader]');

            var uploader = WebUploader.create({
                swf: require.resolve("webuploader").match(/[^?#]*\//)[0] + "Uploader.swf",
                server: this.element.data('uploadUrl'),
                pick: '#answer-stem-uploader',
                formData: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
                accept: {
                    title: 'Images',
                    extensions: 'gif,jpg,jpeg,png',
                    mimeTypes: 'image/*'
                }
            });

            uploader.on( 'fileQueued', function( file ) {
                Notify.info('正在上传，请稍等！', 0);
                uploader.upload();
            });

            uploader.on( 'uploadSuccess', function( file, response ) {
                Notify.success('上传成功！', 1);
                var result = '[image]' + response.hashId + '[/image]';
                editor.insertHtml(result);
            });

            uploader.on( 'uploadError', function( file, response ) {
                Notify.danger('上传失败，请重试！');
            });
        }
    });

    module.exports = EssayQuestion;

});


