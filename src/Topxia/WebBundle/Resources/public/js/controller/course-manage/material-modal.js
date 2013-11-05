define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    var FileChooser = require('../widget/file/file-chooser');

    exports.run = function() {

        var $form = $("#course-material-form");

        var materialChooser = new FileChooser({
            element: '#material-file-chooser'
        });

        materialChooser.on('change', function(item) {
            $form.find('[name="fileId"]').val(item.id);
        });

        $form.on('click', '.delete-btn', function(){
            var $btn = $(this);
            if (!confirm('真的要删除该资料吗？')) {
                return ;
            }

            $.post($btn.data('url'), function(){
                $btn.parents('.list-group-item').remove();
                Notify.success('资料已删除');
            });
        });

        $form.on('submit', function(){
            if ($form.find('[name="fileId"]').val().length == 0) {
                Notify.danger('请先上传文件！');
                return false;
            }
            $.post($form.attr('action'), $form.serialize(), function(html){
                Notify.success('资料上传成功！');
                $("#material-list").append(html).show();
                $form.find('.text-warning').hide();
                $form.find('[name="fileId"]').val('');
                $form.find('[name="description"]').val('');
                materialChooser.open();
            }).fail(function(){
                Notify.success('资料上传失败，请重试！');
            });
            return false;
        });

    };

});