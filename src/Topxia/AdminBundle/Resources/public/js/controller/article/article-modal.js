define(function(require, exports, module) {

    require('ckeditor');
    require('webuploader');
    var Validator = require('bootstrap.validator');
    var Uploader = require('upload');
    require('common/validator-rules').inject(Validator);

    require('jquery.select2-css');
    require('jquery.select2');
    var Notify = require('common/bootstrap-notify');
    exports.run = function() {

        var $form = $("#article-form");
        $modal = $form.parents('.modal');

        var validator = _initValidator($form, $modal);
        var $editor = _initEditorFields($form, validator);

        _initTagSelect($form);

        _uploadAnnex();

       

    };

    $("#article-property-tips").popover({
        html: true,
        trigger: 'hover',
        placement: 'left',
        content: $("#article-property-tips-html").html()
    });

    function _initTagSelect($form) {
        $('#article-tags').select2({

            ajax: {
                url: $('#article-tags').data('matchUrl'),
                dataType: 'json',
                quietMillis: 100,
                data: function(term, page) {
                    return {
                        q: term,
                        page_limit: 10
                    };
                },
                results: function(data) {

                    var results = [];

                    $.each(data, function(index, item) {

                        results.push({
                            id: item.name,
                            name: item.name
                        });
                    });

                    return {
                        results: results
                    };

                }
            },
            initSelection: function(element, callback) {
                var data = [];
                $(element.val().split(",")).each(function() {
                    data.push({
                        id: this,
                        name: this
                    });
                });
                callback(data);
            },
            formatSelection: function(item) {
                return item.name;
            },
            formatResult: function(item) {
                return item.name;
            },
            multiple: true,
            maximumSelectionSize: 20,
            placeholder: "请输入标签",
            width: 'off',
            createSearchChoice: function() {
                return null;
            }
        });
    }

    function _initEditorFields($form, validator) {

        // group: 'default'
        CKEDITOR.replace('richeditor-body-field', {
            toolbar: 'Full',
            filebrowserImageUploadUrl: $('#richeditor-body-field').data('imageUploadUrl'),
            filebrowserFlashUploadUrl: $('#richeditor-body-field').data('flashUploadUrl'),
            height: 300
        });
        
        $("#article_thumb_remove").on('click', function(){
            if (!confirm('确认要删除吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#article-thumb-container").html('');
                $form.find('[name=thumb]').val('');
                $form.find('[name=originalThumb]').val('');
                $btn.hide();
                Notify.success('删除成功！');
            }).error(function(){
                Notify.danger('删除失败！');
            });
        });
    }

    function _initValidator($form, $modal) {
        var validator = new Validator({
            element: '#article-form',
            failSilently: true,
            triggerType: 'change',
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $('#article-operate-save').button('loading').addClass('disabled');
                Notify.success('保存文章成功！');
            }
        });

        validator.addItem({
            element: '[name=title]',
            required: true
        });

        validator.addItem({
            element: '[name=richeditorBody]',
            required: true
        });

        validator.addItem({
            element: '[name=categoryId]',
            required: true
        });   

        validator.addItem({
            element: '[name=sourceUrl]',
            rule: 'url'
        });

        return validator;
    }

    function _uploadAnnex(){
        var $annexbtn = $('#article-annex-upload');
        var formData = $.extend({}, {token:$annexbtn.data('uploadToken')});
        var uploader = WebUploader.create({
           swf: require.resolve("webuploader").match(/[^?#]*\//)[0] + "Uploader.swf",
           server: app.uploadUrl,
           pick: $annexbtn,
           formData: $.extend(formData , {'_csrf_token': $('meta[name=csrf-token]').attr('content') }),
           accept: {
                   title: 'Annex',
                   extensions: 'txt,docx,doc,xls,xlsx,pptx,ppsx,rar,zip',
            }
        });

        uploader.on( 'fileQueued', function( file ) {
            Notify.info('正在上传，请稍等！', 0);
            uploader.upload();
        });

        uploader.on( 'uploadSuccess', function( file, response ) {
                var url = $annexbtn.data("gotoUrl");
                var html = $("#article-annex-container").html();
                var ids = $("#article-annex-ids").val();
                $.post(url, response ,function(data){
                    html += '<p>'+data.fileName+'</p>';
                    ids += data.fileId+',';
                $("#article-annex-container").html(html);
                $("#article-annex-ids").val(ids);
                Notify.success('附件上传成功！');
            });
        });

        uploader.on( 'uploadError', function( file, response ) {
            Notify.danger('上传失败，请重试！');
        });
    }
});