define(function(require,exports,module){

    var Notify = require('common/bootstrap-notify');
    exports.run = function () {
        $('[data-role=delete-carts-btn]').on('click',function(){
                if (!confirm("您真的要移除该课程吗")) {
                    return ;
                };

                var $btn = $(this);
                $.post($btn.data('url'),function(){
                    Notify.success("删除成功");
                    window.location.reload();
                }).error(function(){
                    Notify.danger("删除失败");
                });
        });

        $('[data-role=batch-select]').click(function(){
            if ($(this).is(":checked") == true){
                $('[data-role=single-select]').prop('checked', true);
            } else {
               $('[data-role=single-select]').prop('checked', false);
            }
        });

        $('[data-role=batch-delete-btn]').click(function(){
            var ids = [];

            $('[data-role=single-select]').each(function(index,item){
                console.log(item)
                ids.push($(item).data('id'));
            });

            $.post($(this).data('url'),{ids:ids},function(){
                Notify.success("删除成功");
                window.location.reload();
            }).error(function(){
                    Notify.danger("删除失败");
            });
        });
    }
});
