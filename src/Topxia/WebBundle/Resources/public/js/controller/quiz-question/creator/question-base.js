define(function(require, exports, module) {

    var Widget = require('widget');
    var Handlebars = require('handlebars');
    var Validator = require('bootstrap.validator');
    var Uploader = require('upload');
    var Notify = require('common/bootstrap-notify');
    var EditorFactory = require('common/kindeditor-factory');
    require('common/validator-rules').inject(Validator);

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

            var $trigger = this.$('[data-role=analysis-uploader]');
            var uploader = new Uploader({
                trigger: $trigger,
                name: 'file',
                action: this.element.data('uploadUrl'),
                data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
                accept: 'image/*'
            }).error(function(file) {
                Notify.danger('上传失败，请重试！');
            }).success(function(response) {
                response = $.parseJSON(response);
                var result = '[image]' + response.hashId + '[/image]'
                editor.insertHtml(result);
                Notify.success('上传成功！', 1);
            }).change(function(files) {
                Notify.info('正在上传，请稍等！', 0);
                uploader.submit();
            });
        },

        _initStemField: function() {
            var height = $('#question-stem-field').height();
            var editor = EditorFactory.create('#question-stem-field', this.get('stemEditorName'), {height:height});
            this.get('validator').on('formValidate', function(elemetn, event) {
                editor.sync();
            });

            var $trigger = this.$('[data-role=stem-uploader]');

            var uploader = new Uploader({
                trigger: $trigger,
                name: 'file',
                action: this.element.data('uploadUrl'),
                data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
                accept: 'image/*'
            }).error(function(file) {
                Notify.danger('上传失败，请重试！');
            }).success(function(response) {
                response = $.parseJSON(response);
                var result = '[image]' + response.hashId + '[/image]'
                editor.insertHtml(result);
                Notify.success('上传成功！', 1);
            }).change(function(files) {
                Notify.info('正在上传，请稍等！', 0);
                uploader.submit();
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
                self.get('validator').set('autoSubmit',true);
            });

            return validator;
        }

    });

    module.exports = QuestionCreator;
});