define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);

    var $table = $('#tag-table');

    exports.run = function() {
        $('#tag-create-btn').on('click',function(){
            var $btn = $(this);
            $btn.addClass('disabled');
            $.post($btn.data('url'), $('#tag-create-form').serialize(),function(html) {
                $('#tag').val('');
                $table.find('tbody').prepend(html);
                Notify.success('标签添加成功！');
                $btn.removeClass('disabled');
            });
        });

        $table.on('click','[data-role=update]',function(){
            var self = $(this);
            var selfTr = self.parent().parent();
            selfTr.next('tr').show();
            selfTr.hide();
        });

        $table.on('click','[data-role=delete]',function(){
            var $btn = $(this);
            $btn.addClass('disabled');
            if (!confirm('确定要删除标签吗?')) return ;
            $.post($btn.data('url'),function(response) {
                $btn.parent().parent().prev('tr').remove();
                $btn.parent().parent('tr').remove();
                Notify.success('标签删除成功！');
            }).error(function(){
                Notify.danger('标签删除失败！');
            });
        });

        $table.on('click','[data-role=update-save]',function(){
            var $form = $btn.parent().prev('td').children('form');
            $btn.addClass('disabled');
            $.post($btn.data('url'), $form.serialize(),function(html) {
                $btn.parent().parent().prev('tr').remove();
                $btn.parent().parent('tr').remove();
                $table.find('tbody').prepend(html);
                Notify.success('标签更新成功！');
            }).error(function() {
                Notify.danger("标签更新失败，请重试！");
            });
        });

        $table.on('click','[data-role=update-cancle]',function(){
            var self = $(this);
            var selfTr = self.parent().parent();
            selfTr.prev('tr').show();
            selfTr.hide();
        });
    }
});