define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('jquery.form');
     var Uploader = require('upload');

    exports.run = function() {
        var validator = new Validator({
            element: '#aticel-picture-form'
        });
        $("#article-upload-btn").attr({"disabled":"disabled"});

        $("#article-picture-upload").click(function() {
             $("#article-upload-btn").removeAttr("disabled");
        });

        $("#article-upload-btn").click(function() {

            var $form = $('#aticel-picture-form');
            $(this).html('图片上传中...请稍等');
            $(this).attr({"disabled":"disabled"});
            $form.ajaxSubmit({
                clearForm: true,
                success: function(html){
                    $('#modal').html(html);
                }
            });

        });
       
    };

});