define(function(require, exports, module) {

    exports.run = function() {

        $('.course-join-btn').click(function() {
            $.post($(this).data('url'), function(){
                window.location.reload();
            });
        });

    };

});