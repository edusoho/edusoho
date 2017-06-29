define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function(options) {
        var $table=$('#classroom-table');

        $table.on('click', '.cancel-recommend-classroom', function() {          
            var $trigger = $(this);
            if (!confirm(Translator.trans('%title%吗？',{title:$trigger.attr('title')}))) {
                    return ;
                }
            $.post($(this).data('url'), function(html){
                    Notify.success(Translator.trans('%title%成功！',{title:$trigger.attr('title')}));
                     var $tr = $(html);
                    $('#' + $tr.attr('id')).remove();
                }).error(function(){
                    Notify.danger(Translator.trans('%title%失败!',{title:$trigger.attr('title')}));
                });

        });

    };

});
