define(function(require, exports, module) {

    var Widget = require('widget');
    var Handlebars = require('handlebars');
    var Validator = require('bootstrap.validator');
    var Uploader = require('upload');
    var Notify = require('common/bootstrap-notify');
    var EditorFactory = require('common/kindeditor-factory');
    require('common/validator-rules').inject(Validator);
    require('webuploader');

    var QuestionCreator = Widget.extend({
        attrs: {
            validator : null,
            form : null,
            stemEditorName: 'simple_noimage'
        },

        events: {
            'click [data-role=submit]': 'onSubmit'
        },

        setup: function() {
            this._initForm();
            this._initStemField();
            this._initAnalysisField();
        },

        onSubmit: function(e){
            var submitType = $(e.currentTarget).data('submission');
            this.get('form').find('[name=submission]').val(submitType);
        },

        _initAnalysisField: function() {
            var editor = EditorFactory.create('#question-analysis-field', 'simple_noimage');
            this.get('validator').on('formValidate', function(elemetn, event) {
                editor.sync();
            });


            var uploader = WebUploader.create({
                swf: require.resolve("webuploader").match(/[^?#]*\//)[0] + "Uploader.swf",
                server: this.element.data('uploadUrl'),
                pick: '#question-analysis-uploader',
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

            uploader.disable();

            this.$('#advanced-collapse').on('shown.bs.collapse', function(){
                uploader.enable();
                console.log('shown');
            });

            this.$('#advanced-collapse').on('hidden.bs.collapse', function(){
                console.log('hidden');
                uploader.disable();
            });

        },

        _initStemField: function() {
            var height = $('#question-stem-field').height();
            var editor = EditorFactory.create('#question-stem-field', this.get('stemEditorName'), {height:height});
            this.get('validator').on('formValidate', function(elemetn, event) {
                editor.sync();
            });

            var uploader = WebUploader.create({
                swf: require.resolve("webuploader").match(/[^?#]*\//)[0] + "Uploader.swf",
                server: this.element.data('uploadUrl'),
                pick: '#question-stem-uploader',
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

        },

        _initForm: function() {
            var $form = this.$('[data-role=question-form]');
            this.set('form', $form);
            this.set('validator', this._createValidator($form));
        },

        _createValidator: function($form){
            var self = this;

            Validator.addRule('score',/^(\d){1,10}$/i, '请输入正确的分值');

            validator = new Validator({
                element: $form,
                autoSubmit: false
            });

            validator.addItem({
                element: '#question-stem-field',
                required: true
            });

            validator.addItem({
                element: '#question-score-field',
                required: false,
                rule:'number'
            });

            validator.on('formValidated', function(error, msg, $form) {
                if (error) {
                    return false;
                }

                $('.submit-btn').button('submiting').addClass('disabled');

                self.get('validator').set('autoSubmit',true);
            });

            return validator;
        }

    });

    module.exports = QuestionCreator;
});