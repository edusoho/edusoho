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
            targets: [],
            categories: [],
            stemEditorName: 'simple_noimage'
        },

        events: {
            'click [data-role=submit]': 'onSubmit'
        },

        setup: function() {
            if ($('[data-role=targets-data]').length > 0) {
                this.set('targets', $.parseJSON($('[data-role=targets-data]').html()));
            }

            if ($('[data-role=category-data]').length > 0) {
                this.set('categories', $.parseJSON($('[data-role=category-data]').html()));
            }

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
            var editor = EditorFactory.create('#question-stem-field', this.get('stemEditorName'));
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

            Validator.addRule('fillCheck',/(\[\[(.*?)\]\])/i, '请输入正确的答案,如今天是[[晴|阴|雨]]天.');
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
                rule:'score'
            });

            validator.on('formValidated', function(error, msg, $form) {
                if (error) {
                    return false;
                }
                self.get('validator').set('autoSubmit',true);
            });

            return validator;
        },

        _onChangeCategories: function(categories) {
            var options = "<option value=''>请选择类别</option>";
            var selected = categories['default'] ? categories['default'] : '';

            $.each(categories, function(index, category){
                if (index == 'default') {
                    return ;
                }
                if(category.id == selected){
                    options += '<option selected=selected value=' + category.id + '>' + category.name + '</option>';
                }else{
                    options += '<option value=' + category.id + '>' + category.name + '</option>';
                }
            });

            this.$('[data-role=category]').html(options);
        },

        _onChangeTargets: function(targets) {
            var options = '';
            var selected = targets['default'] ? targets['default'] : '';

            $.each(targets, function(index, target){
                if (index == 'default') {
                    return ;
                }
                if(index == selected){
                    options += '<option selected=selected value=' + index + '>' + target + '</option>';
                }else{
                    options += '<option value=' + index + '>' + target + '</option>';
                }
            });
            
            this.$('[data-role=target]').html(options);
        }

    });

    module.exports = QuestionCreator;
});