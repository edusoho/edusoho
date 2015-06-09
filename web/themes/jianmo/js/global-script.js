define(function(require, exports, module) {

    exports.run = function() {

        var removeNavMobile = function(){
            $(".nav-mobile").removeClass("active");
            $(".html-mask").hide();
            $(".es-wrap").removeClass("nav-active")
            $("html").removeClass("html-nav-active");
        }

        $(".js-navbar-more").click(function(e){
            var $nav = $(".nav-mobile");
            var $mask = $("<div class='html-mask'></div>");
            var $maskItem = $("." + $mask.attr("class"));
            if($nav.hasClass("active")){
                removeNavMobile();

            }else {
                $nav.addClass("active");
                if($maskItem.length == 0){
                $(".es-wrap").append($mask);
                }else{
                    $maskItem.show();
                }
                $(".es-wrap").addClass("nav-active");
                $("html").addClass("html-nav-active");
            }
        });

        $("body").on("click",".html-mask",function(e){
            removeNavMobile();
        });
    }

});