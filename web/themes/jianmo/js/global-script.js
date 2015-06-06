define(function(require, exports, module) {

    exports.run = function() {

        $(".js-navbar-more").click(function(e){
            var $nav = $(".nav-mobile");
            
            if($nav.hasClass("active")){
                $nav.removeClass("active");
                $(".es-wrap").removeClass("nav-active")
                $("html").removeClass("html-nav-active");

            }else {
                $nav.addClass("active");
                $(".es-wrap").addClass("nav-active");
                $("html").addClass("html-nav-active");
            }
        });
    }

});