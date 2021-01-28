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

        $("body").on('click','.js-course-filter',function(){
            var $btn = $(this);
            var courseType = $btn.data('type');
            var text = $('.course-filter .visible-xs .active a').text();
            $.get($btn.data('url'),function(html){
                $('#'+courseType+'-list-section').after(html).remove();
                var parent = $btn.parent();
                if(!parent.hasClass('course-sort')){
                   text = $btn.find("a").text();   
                }
            $('.course-filter .visible-xs .btn').html(text+" "+'<span class="caret"></span>');
                Lazyload.init();
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