define(function(require, exports, module) {
    exports.run = function() {

        /* 猜你喜欢 */
        $('.glyphicon-refresh').on('click', function(){
            $.get($('.my-may-like').data('url'), function(){
                var html = '<p class="pull-text"><a href="#">1.怎么录制高清的课程!</a></p><p class="pull-text"><a href="#">1.怎么录制高清的课程!</a></p><p class="pull-text"><a href="#">1.怎么录制高清的课程!</a></p><p class="pull-text"><a href="#">1.怎么录制高清的课程!</a></p>';
                $('p').fadeOut('slow', function(){
                    $('.my-may-like').find('.panel-body').html(html);
                });
            });
        });


    };

});