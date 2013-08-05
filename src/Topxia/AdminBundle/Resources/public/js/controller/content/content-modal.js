define(function(require, exports, module) {
    "use strict";

	var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('ckeditor');
    require('jquery.select2-css');
    require('jquery.select2');
    require('jquery.bootstrap-datetimepicker');
    var Uploader = require('upload');

	exports.run = function() {
		var $form = $("#content-form"),
            $modal = $form.parents('.modal');
        $form.data('uploading', false);

        var validator = _initValidator($form, $modal);
        _initEditorFields($form, validator);
        _initTagsField();
        _initDatetimeFields($form);
        _initPictureField($form);
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

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    var $newTr = $(html),
                        $oldTr = $("tr#" + $newTr.attr('id'));
                    if ($oldTr.length > 0) {
                        $oldTr.replaceWith($newTr);
                        toastr.success('更新成功!');
                    } else {
                        var $table = $('#content-table tbody').prepend($newTr);
                        toastr.success('添加成功!');
                    }
                    $modal.modal('hide');
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
                rule: 'remote'
            });
        }

        return validator;
    }

    function _initEditorFields($form, validator)
    {
        $form.find('[data-role=editor-field]').each(function(){
            var id = $(this).attr('id');

            CKEDITOR.replace(id, {
                height: 320,
                resize_enabled: false,
                forcePasteAsPlainText: true,
                toolbar: 'Simple',
                removePlugins: 'elementspath',
                filebrowserUploadUrl: '/ckeditor/upload?group=content'
            });

            validator.on('formValidate', function(elemetn, event) {
                CKEDITOR.instances[id].updateElement();
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

    function _initPictureField($form)
    {

        var uploader = new Uploader({trigger: '#picture-upload'});
        var $panel = $('#picture-panel');

        uploader.change(function(filename){
            $form.data('uploading', true);
            $panel.find('[data-role=message]').html('<span class="text-muted">正在上传，请稍等。</span>').fadeIn('slow');
            uploader.submit();
        });

        uploader.success(function(response) {
            var html = '<a href="' + response.url + '" target="_blank">';
            html += '<img src="' + response.url + '" class="img-responsive">';
            html += '</a>';

            $panel.find('input[name=picture]').val(response.uri);
            $panel.find('[data-role=picture-container]').html(html);
            $panel.find('[data-role=message]').html('<span class="text-success">上传成功。</span>').fadeIn('slow');

            setTimeout(function(){
                $panel.find('[data-role=message]').fadeOut('slow');
            }, 3000);
            $form.data('uploading', false);
        });

        uploader.error(function(file){
            $form.data('uploading', false);
        });
    }

});