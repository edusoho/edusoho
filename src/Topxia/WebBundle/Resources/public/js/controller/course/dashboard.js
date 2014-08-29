define(function(require, exports, module) {

    exports.run = function() {
        require('./common').run();
        var Share = require('../../util/share.js');
        require('jquery.cycle2');

        Share.create({
            selector: '.share',
            icons: 'itemsAll',
            display: '',
        });

        $("#course-item-list").on('click', '.lesson-item', function(e) {
            window.location.href = $(this).find('.title').attr('href');
        });

        function checkWidth(){
                if($(this).width()<400){
                $('.name').hide();         
                $('.course-item-list-wrap').css('margin-left','20px');
                $('.pie').css('margin-left','70px');
            }            
            if($(this).width()>=400){
                $('.name').show();
                $('.course-item-list-wrap').css('margin-left','99px');
                $('.pie').css('margin-left','0px');
            }
        }
        $('.announcement-list').on('click', '[data-role=delete]', function(){
            if (confirm('真的要删除该公告吗？')) {
                $.post($(this).data('url'), function(){
                    window.location.reload();
                });
            }
            return false;
        });
        $(document).ready(function(){
           checkWidth();

        });
        $(window).resize(function(){         
           checkWidth();
        });

    };

});