define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');
    
    exports.run = function() {
        var $form = $('#system-icon');
        var $modal = $form.parents('.modal');
        var $uploadForm = $("#icon-upload-form");

        $uploadForm.submit(function(){
            var $uploadForm = $(this);

            var file = $uploadForm.find('[name=file]').val();
            if (!file) {
                Notify.danger('请先选择要上传的图标');
                return false;
            }

            $uploadForm.ajaxSubmit({
                clearForm: true,
                dataType:'json',
                success: function(response){
                    var html = '<img  class="center-block" src="' + response.url + '">';
                    $('[name="uploadIcon"]').val(response.url);
                    $("#iconContent").html(html);
                    Notify.success('插入图标成功！');
                },
                error: function(response) {
                    Notify.danger('上传图标失败，请重试！');
                }
            });

            return false;
        });

        $('#upload-icon-btn').on("click", function() {
            var uploadIcon = $('[name="uploadIcon"]').val();
            var systemIcon = $('[name=system-icon]:checked').val();

            if (uploadIcon) {
                $('[name=icon').val(uploadIcon);
                $('#icon-show').attr('src',uploadIcon);
            } else {
                $('[name=icon').val(systemIcon);
                $('#icon-show').attr('src',systemIcon);
            };
            
            $modal.modal('hide');
            Notify.success('展示图标修改成功');
        })

	};
});