define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var FileChooser = require('../widget/file/file-chooser3');

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
            if (!confirm(Translator.trans('真的要删除该资料吗？'))) {
                return ;
            }

            $.post($btn.data('url'), function(){
                $btn.parents('.list-group-item').remove();
                Notify.success(Translator.trans('资料已删除'));
            });
        });

        $form.on('submit', function(){
            if ($form.find('[name="fileId"]').val().length == 0) {
                Notify.danger(Translator.trans('请先上传文件或添加资料网络链接！'));
                return false;
            }
            $.post($form.attr('action'), $form.serialize(), function(html){
                Notify.success(Translator.trans('资料添加成功！'));
                $("#material-list").append(html).show();
                $form.find('.text-warning').hide();
                $form.find('[name="fileId"]').val('');
                $form.find('[name="link"]').val('');
                $form.find('[name="description"]').val('');
                materialChooser.open();
            }).fail(function(){
                Notify.success(Translator.trans('资料添加失败，请重试！'));
            });
            return false;
        });

        $('.modal').on('hidden.bs.modal', function(){
            window.location.reload();
        });

    };

});