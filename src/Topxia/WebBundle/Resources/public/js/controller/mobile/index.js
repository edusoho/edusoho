define(function(require, exports, module) {

    require("jquery.waypoints");

    exports.run = function() {

        // $('#iphone-download-btn').tooltip()
        $(".js-mobile-item").waypoint(function(){
            $(this).addClass('active');
        },{offset:300});

        $(".es-mobile .btn-mobile").click(function(){
            $('html,body').animate({
                scrollTop: $($(this).attr('data-url')).css('top')
            },1000);
        });

    };

});