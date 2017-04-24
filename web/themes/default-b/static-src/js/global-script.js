define(function(require, exports, module) {

    require('jquery.cycle2');

    exports.run = function() {
        $('.homepage-feature').cycle({
            fx:"scrollHorz",
            slides: "> a, > img",
            log: "false",
            pauseOnHover: "true"
        });
    }
        
/*    $(".js-search").focus(function () {
	        $(this).prop("placeholder", "搜索").addClass("active");
	    }).blur(function () {
	        $(this).prop("placeholder", "").removeClass("active");
	    });
    };*/

});