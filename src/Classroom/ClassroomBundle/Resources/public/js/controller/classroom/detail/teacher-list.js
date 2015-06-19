define(function(require, exports, module) {

    exports.run = function() {
        $('.teacher-item').on('click', '.follow-btn', function(){
            var $btn = $(this);
            $.post($btn.data('url'), function() {
            }).always(function(){
                $btn.hide();
                $btn.closest('.teacher-item').find('.unfollow-btn').show();
            });
        }).on('click', '.unfollow-btn', function(){
            var $btn = $(this);
            $.post($btn.data('url'), function() {
            }).always(function(){
                $btn.hide();
                $btn.closest('.teacher-item').find('.follow-btn').show();
            });
        });
    };

});