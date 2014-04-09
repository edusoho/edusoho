define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Uploader = require('upload');
    require('common/validator-rules').inject(Validator);

    require('jquery.select2-css');
    require('jquery.select2');

    exports.run = function() {
        
        // require('./header').run();

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
    
         var $form = $("#site-form");

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
                // $("#site-logo-container").html('<img src="' + response.url + '">');
                $form.find('[name=picture]').val(response.path);
                // $("#site-logo-remove").show();
                Notify.success('上传picture成功！');
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

});