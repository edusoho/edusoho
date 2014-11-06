define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    exports.run = function() {
        var classList=$(".class-list");
        classList.find('.class-grid').each(function(){
            $(this).hover(
                function(){
                    $(this).find('.class-option').show();
                },
                function(){
                    $(this).find('.class-option').hide();
                }
            );
        });

        classList.on('click','.class-remove',function(){
            if($(this).data('stunum')>0){
                Notify.danger('尚存在学生,请先移除班级学生');
            }else if(confirm('您确定删除吗？')){
                $.get($(this).data('url'),function(){
                    Notify.success('成功移除班级');
                    window.location.reload();
                });
            }
        });

    };
});