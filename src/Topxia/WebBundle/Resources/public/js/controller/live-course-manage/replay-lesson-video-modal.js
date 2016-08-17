define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var FileChooser = require('../widget/file/file-chooser3');

    exports.run = function() {
        var $form = $("#course-material-form");

        var choosedMedia = $form.find('[name="media"]').val();
        choosedMedia = choosedMedia ? $.parseJSON(choosedMedia) : {};

        var materialChooser = new FileChooser({
            element: '#material-file-chooser',
            choosed: choosedMedia
        });

        materialChooser.on('change', function(item) {
            $form.find('[name="fileId"]').val(item.id);
        });

        $form.on('submit', function(){
            if ($form.find('[name="fileId"]').val().length == 0) {
                Notify.danger('请先上传文件！');
                return false;
            }
            $.post($form.attr('action'), $form.serialize(), function(html){
                Notify.success('添加回放成功！');
                $("#material-list").append(html).show();
                $form.find('.text-warning').hide();
                $form.find('[name="fileId"]').val('');
            }).fail(function(){
                Notify.danger('添加回放失败，请重试！');
            });
            return false;
        });

        $('.modal').on('hidden.bs.modal', function(){
            window.location.reload();
        });

    };

});