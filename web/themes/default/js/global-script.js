define(function(require, exports, module) {

  exports.run = function() {
 
    var resizeTimer;

    $(".js-search").focus(function () {
        $(this).prop("placeholder", "搜索").addClass("active");
    }).blur(function () {
        $(this).prop("placeholder", "").removeClass("active");
    });

    function nav() {
        var $navItem = $("#nav .nav-item");
        var $dropdown = $("#nav .nav-more");
        var $dropdownUl = $dropdown.find("ul");
        var navbarWidth = $(".navbar .container").width();
        var thatWidth = 0;
        var sumWidth = 0;
        if ($("body").width() > 760) {
            $navItem.each(function (index, dom) {
                thatWidth = thatWidth + $(dom).outerWidth(true);
            });
            sumWidth = $(".navbar-header").width() + thatWidth + $dropdown.outerWidth(true) + $(".navbar-user").width()+50;

            if (sumWidth > navbarWidth) {
                $dropdown.show().find("ul").empty();
                $navItem.each(function (index, dom) {
                    var navItemLen = parseInt((sumWidth - navbarWidth) / $(dom).outerWidth(true));
                    var mun = $navItem.length - navItemLen - 1 || 1;
                    if (index >= mun) {
                        $(dom).hide();
                        $dropdownUl.append($(dom).clone().show().removeClass("nav-item"));

                    } else {
                        $(dom).show();
                    }
                });
            } else {
                $dropdown.hide();
                $navItem.show();
                $dropdownUl.empty();
            }
        }
    };

    nav();

    $(window).on("resize.nav", function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            nav();
        }, 10);
        removeNavMobile()
    });

    var removeNavMobile = function(){
        $(".nav-mobile").removeClass("active");
        $(".html-mask").hide();
        $("body").removeClass("nav-active")
        $("html").removeClass("html-nav-active");
    }

    $(".js-navbar-more").click(function(e){
        var $nav = $(".nav-mobile");
        var $mask = $("<div class='html-mask'></div>");
        var $maskItem = $("." + $mask.attr("class"))
        if($nav.hasClass("active")){
            removeNavMobile()
        }else{
            $nav.addClass("active");
            if($maskItem.length == 0){
                $("body").append($mask)
            }else{
                $maskItem.show()
            }
             $("body").addClass("nav-active")
            $("html").addClass("html-nav-active");
        }
    })

    $("body").on("click",".html-mask",function(e){
        removeNavMobile();
    });

    $('[data-toggle="tooltip"]').tooltip();


  }

});