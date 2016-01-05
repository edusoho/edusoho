define(function(require, exports, module) {

    require('es-ckeditor');
    var WebUploader = require('edusoho.webuploader');
    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    Validator.addRule(
         'time_check',
         /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29) ([0-1]{1}[0-9]{1})|(2[0-4]{1}):[0-5]{1}[0-9]{1}$/, 
         '请输入正确的日期和时间,格式如XXXX-MM-DD hh:mm'
     );
    var Widget = require('widget');
    require('common/validator-rules').inject(Validator);
    require("jquery.bootstrap-datetimepicker");

    exports.run = function() {
        var ThreadForm = Widget.extend({
            events: {
                'change [name=type]': 'onChangesTypeSelect'
            },

            setup: function() {
                this._initValidator();
                this._initDatetimepicker();
                this._initUploader();
            },
            _initValidator: function() {
                var editor = CKEDITOR.replace('thread-content-field', {
                    toolbar: 'Thread',
                    filebrowserImageUploadUrl: $('#thread-content-field').data('imageUploadUrl')
                });

                var validator = new Validator({
                    element: '#thread-form'
                });

                validator.addItem({
                    element: '[name="title"]',
                    required: true,
                    rule: 'visible_character'
                });

                validator.addItem({
                    element: '[name="content"]',
                    required: true
                });

                if (this.$('[name="type"]').val() == 'event') {
                    validator.addItem({
                        element: '[name="maxUsers"]',
                        rule: 'positive_integer'
                    });
                    validator.addItem({
                        element: '[name="location"]',
                        required: false,
                        rule: 'visible_character'
                    });
                    validator.addItem({
                        element: '[name="startTime"]',
                        required: true,
                        rule: 'time_check'
                    });

                    this.$("[name='startTime']").change(function() {
                        validator.query('[name=startTime]').execute(function(error, results, element) {
                        });            
                    });

                    this.$("[name='startTime']").blur(function() {
                        validator.query('[name=startTime]').execute(function(error, results, element) {
                        });
                    });   
                }
      

                validator.on('formValidate', function(elemetn, event) {
                    editor.updateElement();
                });

                validator.on('formValidated', function(err, msg, $form) {
                    if (err === true) {
                        return;
                    }

                    $form.find('[type=submit]').attr('disabled', 'disabled');

                    return true;
                });

                this.validator = validator;
            },
            _initDatetimepicker: function() {
                this.$("#startTime").datetimepicker({
                    language: 'zh-CN',
                    autoclose: true,
                    format: 'yyyy-mm-dd hh:ii',
                    minView: 'hour'
                }); 
            },
            _initUploader: function() {
                var self = this;
                var $uploadBtn = this.$('#js-activity-uploader');
                if ($uploadBtn.length > 0) {
                    var uploader = new WebUploader({
                       element: '#js-activity-uploader'
                    });

                   uploader.on('uploadSuccess', function(file, response ) {
                        self.$('[name=actvityPicture]').val(response.url);
                        Notify.success('上传成功！', 1);
                   });

                }
            }
        });
        
        new ThreadForm({
            'element': '#thread-form'
        });

    };

});