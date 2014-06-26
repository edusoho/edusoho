define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
	require('common/validator-rules').inject(Validator);
    require('webuploader');
    
	exports.run = function() {
        var $form = $('#category-form');
		var $modal = $form.parents('.modal');
        var $table = $('#category-table');

        var uploader = WebUploader.create({
            swf: require.resolve("webuploader").match(/[^?#]*\//)[0] + "Uploader.swf",
            server: $('#category-creater-widget').data('uploadUrl'),
            pick: '#category-icon-uploader',
            formData: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: {
                title: 'Images',
                extensions: 'gif,jpg,jpeg,png',
                mimeTypes: 'image/*'
            }

        });

        uploader.on( 'fileQueued', function( file ) {
            Notify.info('正在上传，请稍等！', 0);
            uploader.upload();
        });

        uploader.on( 'uploadSuccess', function( file, response ) {
            Notify.success('上传成功！', 1);
            $('#category-icon-field').html('<img src="' + response.hashId + '">');
            $('#category-icon-field').addClass('mbm');
            $form.find('[name=icon]').val(response.hashId);
            $("#category-icon-delete").show();
        });

        uploader.on( 'uploadError', function( file, response ) {
            Notify.danger('上传失败，请重试！');
        });

        $("#category-icon-delete").on('click', function(){
            if (!confirm('确认要删除图标吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#category-icon-field").html('');
                $form.find('[name=icon]').val('');
                $btn.hide();
                $('#category-icon-field').removeClass('mbm');
                Notify.success('删除分类图标成功！');
            }).error(function(){
                Notify.danger('删除分类图标失败！');
            });
        });

		var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }

                $('#category-create-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html){
                    $modal.modal('hide');
                    $table.find('tbody').replaceWith(html);
                    Notify.success('保存分类成功！');
				}).fail(function() {
                    Notify.danger("添加分类失败，请重试！");
                });

            }
        });

        validator.addItem({
            element: '#category-name-field',
            required: true,
            rule: 'maxlength{max:100}'
        });

        validator.addItem({
            element: '#category-code-field',
            required: true,
            rule: 'alphanumeric not_all_digital remote'
        });

        validator.addItem({
            element: '#category-weight-field',
            required: false,
            rule: 'integer'
        });

        $modal.find('.delete-category').on('click', function() {
            if (!confirm('真的要删除该分类及其子分类吗？')) {
                return ;
            }

            $.post($(this).data('url'), function(html) {
                $modal.modal('hide');
                $table.find('tbody').replaceWith(html);
            });

        });

	};

});