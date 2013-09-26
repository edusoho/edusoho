define(function(require, exports, module) {

    var $=require('jquery');
    require('jquery.slides');
    require('jquery.slides-css');
    
    exports.run = function() {

        $(function(){
            $('#slides').slides({
                preload: true,
                preloadImage: '../../bundles/topxiaweb/img/loading.gif',
                play: 5000,
                pause: 2500,
                hoverPause: true,
                fadeSpeed: 350,
                effect: 'fade'
            });
        });

    }

});