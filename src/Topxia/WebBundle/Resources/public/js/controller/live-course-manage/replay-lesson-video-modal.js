define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var VideoChooser = require('../widget/media-chooser/video-chooser7');

    exports.run = function() {
        var $form = $("#course-material-form");

        var choosedMedia = $form.find('[name="media"]').val();
        choosedMedia = choosedMedia ? $.parseJSON(choosedMedia) : {};

        var videoChooser = new VideoChooser({
            element: '#material-file-chooser',
            choosed: choosedMedia
        });

        videoChooser.show();

        videoChooser.on('change', function(item) {
            $form.find('[name="fileId"]').val(item.id);
            var value = item ? JSON.stringify(item) : '';
            $form.find('[name="media"]').val(value);
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
                $('.modal').modal('hide');
                window.location.reload();
            }).fail(function(){
                Notify.danger('添加回放失败，请重试！');
            });
            return false;
        });

    };

});