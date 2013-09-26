define(function(require, exports, module) {
    "use strict";

	var Validator = require('bootstrap.validator');
    
    Validator.addRule(
        'noNumberFirst',
        /^[a-zA-Z]+[a-zA-Z0-9]+?$/,
        'URL路径不能以数字开头'
    );

    var Notify = require('common/bootstrap-notify');
    var EditorFactory = require('common/kindeditor-factory');
    require('common/validator-rules').inject(Validator);
    require('jquery.select2-css');
    require('jquery.select2');
    require('jquery.bootstrap-datetimepicker');
    require('jquery.form');

	exports.run = function() {
		var $form = $("#content-form"),
            $modal = $form.parents('.modal');
        $form.data('uploading', false);

        var validator = _initValidator($form, $modal);
        _initEditorFields($form, validator);
        _initTagsField();
        _initDatetimeFields($form);
	};

    function _initValidator($form, $modal)
    {
        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }

                if ($form.data('uploading')) {
                    alert('正在上传附图，请等待附图上传成功后，再保存！');
                    return ;
                }

                $form.ajaxSubmit({
                    clearForm: true,
                    success: function(data){
                        $modal.modal('hide');
                        window.location.reload();
                    }
                });
            }
            
        });

        if ($form.find('[name="title"]').length > 0) {
            validator.addItem({
                element: '[name="title"]',
                required: true
            });
        }        

        if ($form.find('[name="alias"]').length > 0) {
            validator.addItem({
                element: '[name="alias"]',
                rule: 'remote noNumberFirst'
            });
        }

        return validator;
    }

    function _initEditorFields($form, validator)
    {
        $form.find('[data-role=editor-field]').each(function(){
            var id = $(this).attr('id');

            var editor = EditorFactory.create('#' + id, 'full', {extraFileUploadParams:{group:'default'}});

            validator.on('formValidate', function(elemetn, event) {
                editor.sync();
            });

        });
    }

    function _initTagsField()
    {
        if ($('#content-tags-field').length < 1) {
            return ;
        }

        require.async('/tag/all.jsonm#', function(tags) {
            $('#content-tags-field').select2({
                width: 'off',
                multiple: true,
                maximumSelectionSize: 20,
                id: 'name',
                data: {results:tags, key:'name'},
                formatSelection: function(item) {
                    return item.name;
                },
                formatResult: function(item) {
                    return item.name;
                },
                initSelection : function (element, callback) {
                    var data = [];
                    $(element.val().split(",")).each(function () {
                        data.push({id: this, name: this});
                    });
                    callback(data);
                }
            });
        });
    }

    function _initDatetimeFields($form)
    {
        $form.find('[data-role=datetime-field]').each(function(){
            $(this).datetimepicker({
                format: 'yyyy-mm-dd hh:ii',
                pickerPosition: "top-right",
                autoclose: true,
                minuteStep: 10
            });
        });
    }

});