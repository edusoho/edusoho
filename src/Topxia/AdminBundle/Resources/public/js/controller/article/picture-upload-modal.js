define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('jquery.form');
     var Uploader = require('upload');

    exports.run = function() {
        var validator = new Validator({
            element: '#aticel-picture-form'
        });

        validator.addItem({
            element: '[name="picture"]',
            required: true,
            requiredErrorMessage: '请选择要上传的头像文件。'
        });

        // $('.use-partner-avatar').on('click', function(){
        //     var goto = $(this).data('goto');
        //     $.post($(this).data('url'), function(){
        //         window.location.href = goto;
        //     });
        // });

        $("#article-upload-btn").click(function() {

            var $form = $('#aticel-picture-form');

            $form.ajaxSubmit({
                clearForm: true,
                 name: 'picture',
                success: function(html){
                    $('#modal').html(html);
                }
            });

        });
        // var uploader = new Uploader({
        //     trigger: '#article-upload-btn',
        //     name: 'picture',
        //     action: $('#article-upload-btn').data('url'),
        //     accept: 'image/*',
        //     error: function(file) {
        //         Notify.danger('上传picture失败，请重试！')
        //     },
        //     success: function(response) {
        //       //   response = eval("(" + response + ")");
        //       //   console.log(response);
        //       // console.log($form.find('#article-pic').val());
        //       //   $("#article-picture-container").html('<img src="' + response.url + '" style="margin-bottom: 10px;">');
        //       //   $form.find('#article-pic').val(response.url);
        //       // console.log($form.find('#article-pic').val());
        //       //    Notify.success('上传成功！');
        //     }
        // });
    };

});