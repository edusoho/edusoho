define(function(require, exports, module) {

    exports.run = function() {
        $('.schedule-panel').delegate('.nav-tabs li','click',function(){
            $.get($(this).data('url'),function(html){
                $('.schedule-panel .panel-body').html(html);
            });
        });
    }
});
