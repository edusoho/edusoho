define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    var EditorFactory = require('common/kindeditor-factory');
	require('common/validator-rules').inject(Validator);
    require('jquery.form');

	exports.run = function() {
		var $form = $('#userlevel-form');
        var $table = $('#userlevel-table');

		var validator = new Validator({
            element: $form
        });

     /*   $("#changeButton").on('click', function() {
            $("#icon_uploadform_area").show();
            $(this).hide();
        })

        $("#uploadButton").on('click',function(){

            var $uploadForm = $("#icon-image-upload-form");

            var file = $uploadForm.find('[name=file]').val();
            if (!file) {
                Notify.danger('请先选择要上传的图片');
                return false;
            }

            $uploadForm.ajaxSubmit({
                clearForm: true,
                dataType:'json',
                success: function(response){
                    var html = response.url;
                    $("#icon-container").html('<img src="' + response.url + '">');
                    $("#levelIcon").val(html);
                    Notify.success('插入图标成功！');
                },
                error: function(response) {
                    Notify.danger('上传图标失败，请重试！');
                }
            });

            return false;
        });*/

        validator.addItem({
            element: '[name="name"]',
            required: true,
            rule: 'remote'
        });

        validator.addItem({
            element: '[class="boughtType"]',
            required: true
        });



 /*       var editor = EditorFactory.create('#userlevel-content-field', 'standard', {extraFileUploadParams:{}, height: '300px'});
            
            validator.on('formValidate', function(elemetn, event) {
                editor.sync();
            });*/
    };

});