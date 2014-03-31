define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');
    
    exports.run = function() {
        var $form = $('#system-picture');
        var $modal = $form.parents('.modal');
        var $uploadForm = $("#picture-upload-form");

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
            var systemPicture = $('[name=system-picture]:checked').val();

            if (uploadPicture) {
                $('[name=picture').val(uploadPicture);
                $('#picture-show').attr('src',uploadPicture);
            } else {
                $('[name=picture').val(systemPicture);
                $('#picture-show').attr('src',systemPicture);
            };
            
            $modal.modal('hide');
            Notify.success('展示图片修改成功');
        })

	};
});