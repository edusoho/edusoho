define(function(require, exports, module) {

    exports.run = function() {
        $('.js-video-delete-btn').click(function(){
            var url = $(this).data('url');

            $.post(url, {}, function(){
                $('.js-delete-video-btn').addClass('disabled');
                $('#modal').modal('hide');
            })
        })
    };

});