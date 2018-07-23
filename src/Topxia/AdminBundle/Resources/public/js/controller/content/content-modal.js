define(function(require, exports, module) {
    "use strict";

	var Validator = require('bootstrap.validator');
    require('es-ckeditor');
    
    Validator.addRule(
        'noNumberFirst',
        /^[a-zA-Z]+[a-zA-Z0-9]+?$/,
        Translator.trans('validate.no_number_first_hint')
    );

    var Notify = require('common/bootstrap-notify');
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
        var editor = _initEditorFields($form, validator);
        _initTagsField();
        _initDatetimeFields($form);
        _changeEditor(editor);

        $('[data-toggle="tooltip"]').popover();
	};

    function _changeEditor(editor)
    {
        $('input[name="editor"]:radio').change(
            function(){
               
               var editorType = $(this).val();
               var valueInHtml = $('#noneeditor-body-field').val();
               var valueInrichEditor = editor.getData();
               

               if(editorType == 'richeditor'){
                editor.setData(valueInHtml);
                $('#richeditor-body-field').parents('.form-group').show();
                $('#noneeditor-body-field').parents('.form-group').hide();

               } else if(editorType == 'none'){

                $('#noneeditor-body-field').val(valueInrichEditor);
                $('#noneeditor-body-field').parents('.form-group').show();
                $('#richeditor-body-field').parents('.form-group').hide();

               }
            }
        ); 
    }

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
                    alert(Translator.trans('admin.content.upload_hint'));
                    return ;
                }
                
                $('#content-save-btn').button('loading').addClass('disabled');
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

        // group: 'default'
        var editor = CKEDITOR.replace('richeditor-body-field', {
            toolbar: 'Admin',
            allowedContent: true,
            filebrowserImageUploadUrl: $('#richeditor-body-field').data('imageUploadUrl'),
            filebrowserFlashUploadUrl: $('#richeditor-body-field').data('flashUploadUrl'),
            height: 300
        });

        validator.on('formValidate', function(elemetn, event) {
            editor.updateElement();
        });

        return editor;
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