define(function(require, exports, module) {

    require('ckeditor');
    var Validator = require('bootstrap.validator');
    var Share = require('../../util/share.js');
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
            },
            onChangesTypeSelect: function(e) {
                var $currentTarget = $(e.currentTarget);
                if ($currentTarget.val() == 'activity') {
                    this.$('.js-activity-content').slideDown();
                } else {
                    this.$('.js-activity-content').slideUp();
                }
            },
            _initValidator: function() {
                var editor = CKEDITOR.replace('thread-content-field', {
                    toolbar: 'Simple',
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
                    format: 'yyyy-mm-dd HH:ii',
                    minView: 'hour'
                }); 
            }
        });
        
        new ThreadForm({
            'element': '#thread-form'
        });

    };

});