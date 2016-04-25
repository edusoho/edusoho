define(function(require, exports, module) {

    var SlidePlayer = require('../../../../../topxiaweb/js/controller/widget/slider-player');

    exports.run = function() {
        var player = $("#ppt-player");

        $.get(player.data('url'), function(response) {

            var html = '<div class="slide-player"><div class="slide-player-body loading-background"></div><div class="slide-notice"><div class="header">已经到最后一张图片了哦<button type="button" class="close">×</button></div></div><div class="slide-player-control clearfix"><a href="javascript:" class="goto-first"><span class="glyphicon glyphicon-step-backward"></span></a><a href="javascript:" class="goto-prev"><span class="glyphicon glyphicon-chevron-left"></span></a><a href="javascript:" class="goto-next"><span class="glyphicon glyphicon-chevron-right"></span></span></a><a href="javascript:" class="goto-last"><span class="glyphicon glyphicon-step-forward"></span></a><a href="javascript:" class="fullscreen"><span class="glyphicon glyphicon-fullscreen"></span></a><div class="goto-index-input"><input type="text" class="goto-index form-control input-sm" value="1">&nbsp;/&nbsp;<span class="total"></span></div></div></div>';
            $("#ppt-player").html(html).show();

            var player = new SlidePlayer({
                element: '.slide-player',
                slides: response.images
            });
        }, 'json');
    }
});