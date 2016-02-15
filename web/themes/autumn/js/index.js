define(function(require, exports, module) {

    var Lazyload = require('echo.js');
    exports.run = function() {
        Lazyload.init();
        
        var carousel = function() {

            var $this = $("#autumn-carousel .carousel-inner .item");

            for (var i = 0; i < $this.length; i++) {
              if (i == 0) {
                var html = '<li data-target="#autumn-carousel" data-slide-to="0" class="active"></li>';
                $this.parents(".carousel-inner").siblings(".carousel-indicators").append(html);
              }else {
                var html = '<li data-target="#autumn-carousel" data-slide-to="'+i+'"></li>';
                $this.parents(".carousel-inner").siblings(".carousel-indicators").append(html);
              }
            }

        }
        carousel();
    }
});