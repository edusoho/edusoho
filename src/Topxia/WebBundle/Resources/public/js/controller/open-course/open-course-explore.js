define(function(require, exports, module) {
    "use strict";
    var Lazyload = require('echo.js');

    var getPageList = function (url){
        $.post(url, function(response){
            $(".section-more-btn").remove();
            $('.open-course-list').append(response);
            Lazyload.init();
        })
    };

    exports.run = function() {
        var $list = $('.open-course-list');
        $list.on('click','.section-more-btn a',function(){
        	var url = $(this).attr('data-url');
	      	getPageList(url);
        });

        getPageList($list.data('url'));
    };

});