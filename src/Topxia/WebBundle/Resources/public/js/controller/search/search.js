define(function(require, exports, module) {

    var Lazyload = require('echo.js');
    require('topxiawebbundle/util/follow-btn');
    require('jquery.lavaTab');

    exports.run = function() {

        Lazyload.init();

        $('.nav-tabs').lavaTab({
        	fx: "backout",
        	speed: 700
        });

        $(".js-btn-clear").on("click",function(){
        	var $this = $(this);
        	
        })

        $('.nav-tabs').on('click','li:not(".active")>a',function(){
        	var $this = $(this);
        	var url = $this.data(url);
        	$.get(url,function(html){
    			$(".search-result").html(html);
        	})
        })

    };

});