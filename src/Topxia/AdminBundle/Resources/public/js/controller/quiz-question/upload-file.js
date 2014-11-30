define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require('jquery.form');

    exports.run = function() {
        $("#quiz-upload-form").on('click', '#uploadBtn', function(){

            var $uploadForm = $("#quiz-upload-form");

            var file = $uploadForm.find('[name=uploadFile]').val();
            if (!file) {
                Notify.danger('请先选择要上传的图片');
                return false;
            }
            $uploadForm.ajaxSubmit({
                clearForm: true,
                dataType:'json',
                success: function(response){
                    var html = '[' + response.type + ']' + response.hashId + '[/' + response.type + ']';

                    $('#question-stem-field').append(html);
                    Notify.success('上传图片成功！');
                },
                error: function(response) {
                    Notify.danger('上传图片失败，请重试！');
                }
            });
        });

    };

});



















