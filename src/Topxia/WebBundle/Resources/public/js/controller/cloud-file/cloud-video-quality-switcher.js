define(function(require, exports, module) {
    var VideoQualitySwitcher = require('../widget/video-quality-switcher');

    exports.run=function(){
    	if ($('.video-quality-switcher').length > 0) {
            var switcher = new VideoQualitySwitcher({
                element: '.video-quality-switcher'
            });
        }
    }
});