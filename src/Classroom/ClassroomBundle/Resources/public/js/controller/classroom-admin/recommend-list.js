define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function(options) {
        var $table=$('#classroom-table');

        $table.on('click', '.cancel-recommend-classroom', function() {          
            var $trigger = $(this);
            if (!confirm($trigger.attr('title') + '吗？')) {
                    return ;
                }
            $.post($(this).data('url'), function(html){
                    Notify.success($trigger.attr('title') + '成功！');
                     var $tr = $(html);
                    $('#' + $tr.attr('id')).remove();
                }).error(function(){
                    Notify.danger($trigger.attr('title') + '失败');
                });

        });

    };

});
