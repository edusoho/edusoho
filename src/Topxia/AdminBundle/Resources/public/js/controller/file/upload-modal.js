define(function(require, exports, module) {

    require('jquery.form');

	exports.run = function() {

        var $form = $('#file-upload-form'),
            $modal = $form.parents('.modal');

        $form.ajaxForm({
            dataType: 'json',
            clearForm: true,
            beforeSubmit: function() {
                if ($form.find('[name=file]').val() == '') {
                    alert('请选择要上传的文件。');
                    return false;
                }
                $modal.find('[type=submit]').button('loading');
            },
            success: function(data){
                $modal.find('[type=submit]').button('next');
                var html = '';
                html += '<a href="' + data.url + '" target="_blank">';
                if (data.mime.indexOf('image/') === 0) {
                    html += '<img src="' + data.url + '" style="max-width:200px;max-height:200px;" />';
                } else {
                    html += '<span class="control-text">点击查看文件</span>';
                }
                html += '</a>';
                $("#file-uploaded-control-group .controls").html(html);
            }
        });

        $modal.on('hidden', function(){
            window.location.reload();
        });

	};

});