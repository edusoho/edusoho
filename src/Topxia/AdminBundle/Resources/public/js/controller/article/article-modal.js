define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Uploader = require('upload');
    var EditorFactory = require('common/kindeditor-factory');
    require('common/validator-rules').inject(Validator);

    require('jquery.select2-css');
    require('jquery.select2');
    var Notify = require('common/bootstrap-notify');
    exports.run = function() {
        
            var $editor = _initEditorFields($form, validator);
            $('#article-tags').select2({
            
                ajax: {
                    url: $('#article-tags').data('matchUrl'),
                    dataType: 'json',
                    quietMillis: 100,
                    data: function (term, page) { 
                        return {
                            q: term, 
                            page_limit: 10
                        };
                    },
                    results: function (data) {

                        var results = [];

                        $.each(data, function(index, item){

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
                initSelection : function (element, callback) {
                    var data = [];
                    $(element.val().split(",")).each(function () {
                        data.push({id: this, name: this});
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
                createSearchChoice: function() { return null; },
            });
    
         var $form = $("#article-form");

        var uploader = new Uploader({
            trigger: '#article-pic-upload',
            name: 'picture',
            action: $('#article-pic-upload').data('url'),
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传picture失败，请重试！')
            },
            success: function(response) {
                response = eval("(" + response + ")");
                console.log(response);
              console.log($form.find('#article-pic').val());
                $("#article-picture-container").html('<img src="' + response.url + '" style="margin-bottom: 10px;">');
                $form.find('#article-pic').val(response.url);
              console.log($form.find('#article-pic').val());
                 Notify.success('上传成功！');
            }
        });

        var validator = new Validator({
            element: '#article-form',
            failSilently: true,
            triggerType: 'change'
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

    };

  function _initEditorFields($form, validator)
    {
        
        var editor = EditorFactory.create('#richeditor-body-field', 'full', {extraFileUploadParams:{group:'default'}});
        validator.on('formValidate', function(elemetn, event) {
            editor.sync();
        });

        return editor;
    }
});