define(function(require, exports, module) {

    exports.run = function() {

        var removeNavMobile = function(){
            $(".nav-mobile").removeClass("active");
            // $(".html-mask").hide();
            $("body").removeClass("nav-active")
            $("html").removeClass("html-nav-active");
        }

        $(".js-navbar-more").click(function(e){
            var $nav = $(".nav-mobile");
            // var $mask = $("<div class='html-mask'></div>");
            // var $maskItem = $("." + $mask.attr("class"));
            if($nav.hasClass("active")){
                removeNavMobile();
            }else{
                console.log($nav);
                $nav.addClass("active");
                // if($maskItem.length == 0){
                //     $("body").append($mask);
                // }else{
                //     $maskItem.show();
                // }
                $("body").addClass("nav-active");
                $("html").addClass("html-nav-active");
            }
        })

        // $("html").on("click",'body.nav-active',function(e){
        //     removeNavMobile();
        // });
        

    }

});