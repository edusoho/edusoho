define(function(require, exports, module) {
    exports.run = function() {
        $('.schedule-panel .nav-tabs').on('click','li',function(){
            $.get($(this).data('url'),function(html){
                $('.schedule-panel .panel-body').html('');
                $('.schedule-panel .panel-body').append(html);
            });
        });
    }
});
