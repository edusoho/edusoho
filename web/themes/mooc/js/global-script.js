define(function(require, exports, module) {

    exports.run = function() {

        var removeNavMobile = function(){
            $(".nav-mobile").removeClass("active");
            $(".html-mask").removeClass("active");
            $(".es-wrap").removeClass("nav-active")
            $("html").removeClass("html-nav-active").css("height",'auto');
        }

        $(".js-navbar-more").click(function(e){
            var $nav = $(".nav-mobile");
            
            if($nav.hasClass("active")){
                removeNavMobile();

            }else {
                var height = $(window).height();
                $("html").css("height",height);
                $nav.addClass("active").css("height",height);
                
                $(".html-mask").addClass("active");
                $(".es-wrap").addClass("nav-active");
                $("html").addClass("html-nav-active");
            }
        });

        $("body").on("click",'.nav-mobile.active',function(e){
            e.stopPropagation(); 
        });

        $("body").on("click",'.html-mask.active',function(e){
            removeNavMobile();
        });
        
    }

});