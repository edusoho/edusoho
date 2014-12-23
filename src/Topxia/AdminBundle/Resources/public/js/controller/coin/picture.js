define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');
    
    exports.run = function() {
        var $modal = $('#system-picture').parents('.modal');
        var $uploadForm = $("#picture-upload-form");
        var $form = $("#coin-settings-form");

        $uploadForm.submit(function(){
            var $uploadForm = $(this);

            var file = $uploadForm.find('[name=file]').val();
            if (!file) {
                Notify.danger('请先选择要上传的图片');
                return false;
            }

            $uploadForm.ajaxSubmit({
                clearForm: true,
                dataType:'json',
                success: function(response){
                    var html = '<img class="col-md-12" src="' + response.url + '">';
                    $('[name="uploadPicture"]').val(response.url);
                    $("#pictureContent").html(html);
                    Notify.success('插入图片成功！');
                },
                error: function(response) {
                    Notify.danger('上传图片失败，请重试！');
                }
            });

            return false;
        });

        $('#upload-picture-btn').on("click", function() {
            var uploadPicture = $('[name="uploadPicture"]').val();

            if (uploadPicture) {
                $form.find('[name=coin_picture]').val(uploadPicture);
                $form.find('#picture-show').attr('src',uploadPicture);
            } else {
                $form.find('[name=coin_picture]').val('');
                $form.find('#picture-show').attr('src','');
            };
            
            $modal.modal('hide');
            Notify.success('展示图片修改成功');
        })

	};
});