define(function(require, exports, module) {

    var Lazyload = require('echo.js');

    var Swiper = require('swiper');

    exports.run = function() {
        if ($(".es-poster .swiper-slide").length > 1) {
            var swiper = new Swiper('.es-poster.swiper-container', {
                pagination: '.swiper-pager',
                paginationClickable: true,
                autoplay: 5000,
                autoplayDisableOnInteraction: false,
                loop: true,
                calculateHeight: true,
                roundLengths: true,
                onInit: function(swiper) {
                   $(".swiper-slide").removeClass('swiper-hidden'); 
                }
            });
        }
        Lazyload.init();

        $("#course-list").on('click','.js-course-filter',function(){
             var $btn = $(this);
             $.get($btn.data('url'),function(html){
               $('#course-list').html(html);
               Lazyload.init();
               $(".tooltip").remove();
               $('[data-toggle="tooltip"]').tooltip({container: 'body'});
            })
        })

    

        $('.recommend-teacher').on('click', '.teacher-item .follow-btn', function(){
            var $btn = $(this);
            var loggedin = $btn.data('loggedin');
            if(loggedin == "1"){
                showUnfollowBtn($btn);
            }
            $.post($btn.data('url'));
        }).on('click', '.teacher-item .unfollow-btn', function(){
            var $btn = $(this);
            showFollowBtn($btn);
            $.post($btn.data('url'));
        })


        function showFollowBtn($btn)
        {
            $btn.hide();
            $btn.siblings('.follow-btn').show();
            $actualCard = $('#user-card-'+ $btn.closest('.js-card-content').data('userId'));
            $actualCard.find('.unfollow-btn').hide();
            $actualCard.find('.follow-btn').show();
        }

        function showUnfollowBtn($btn)
        {
            $btn.hide();
            $btn.siblings('.unfollow-btn').show();
            $actualCard = $('#user-card-'+ $btn.closest('.js-card-content').data('userId'));
            $actualCard.find('.follow-btn').hide();
            $actualCard.find('.unfollow-btn').show();
        }
        
    };

});