define(function(require, exports, module) {

    $('body').on('click', '.teacher-item .follow-btn', function(){
        var $btn = $(this);
        $.post($btn.data('url'), function() {
            var loggedin = $btn.data('loggedin');
            if(loggedin == "1"){
                $btn.hide();
                $btn.closest('.teacher-item').find('.unfollow-btn').show();
            }
        });
    }).on('click', '.unfollow-btn', function(){
        var $btn = $(this);
        $.post($btn.data('url'), function() {
        }).always(function(){
            $btn.hide();
            $btn.closest('.teacher-item').find('.follow-btn').show();
        });
    });

});