define(function(require, exports, module) {

    var Lazyload = require('echo.js');

    var Swiper = require('swiper');

    exports.run = function() {
        var swiper = new Swiper('.poster.swiper-container', {
            pagination: '.swiper-pager',
            swipeToPrev : false,
            swipeToNext : false,
            paginationClickable: true
        });

        Lazyload.init();

        $("#course-list").on('click','.js-search',function(){
             var $btn = $(this);
             $.get($btn.data('url'),function(html){
               $('#course-list').html(html);
               Lazyload.init();
            })
        })
        
    };

});