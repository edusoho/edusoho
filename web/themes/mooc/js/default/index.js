define(function(require, exports, module) {
    exports.run = function(){

    };

    $('.js-course-filter').on('click', function(){
        $(this).siblings(".active").removeClass('active');
        $(this).addClass('active');
        $.get($(this).data('url'),function(html){
            $(".home-course-list").html(html);
            Lazyload.init();
        });

    });
});
