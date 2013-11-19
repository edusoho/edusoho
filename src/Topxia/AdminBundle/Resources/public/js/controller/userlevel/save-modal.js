define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
	require('common/validator-rules').inject(Validator);
    require('jquery.form');

	exports.run = function() {
		var $form = $('#userlevel-form');
		var $modal = $form.parents('.modal');
        var $table = $('#userlevel-table');

		var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }

                $.post($form.attr('action'), $form.serialize(), function(html){
                    var $html = $(html);
                    if ($table.find( '#' +  $html.attr('id')).length > 0) {
                        $('#' + $html.attr('id')).replaceWith($html);
                        Notify.success('会员等级更新成功！');
                    } else {
                        $table.find('tbody').prepend(html);
                        Notify.success('会员等级添加成功!');
                    }
                    $modal.modal('hide');
				});

            }
        });

        $("#changeButton").on('click', function() {
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
        });

        validator.addItem({
            element: '[name="Name"]',
            required: true,
            rule: 'remote'
        });

        validator.addItem({
            element: '[name="Icon"]',
            required: true,
        });

        $modal.find('.delete-tag').on('click', function() {
            if (!confirm('真的要删除该会员等级吗？')) {
                return ;
            }

            var trId = '#userlevel-' + $(this).data('userlevelId');
            $.post($(this).data('url'), function(html) {
                $modal.modal('hide');
                $table.find(trId).remove();
            });

        });

	};




});