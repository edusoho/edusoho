define(function(require, exports, module) {

    var Widget = require('widget');
    var chapterAnimate = Widget.extend({
        events: {
            'click .period-list .chapter': 'onClickChapter'
        },

        setup: function() {

        },

        onClickChapter: function(e) {
            var $target = $(e.currentTarget);
            $target.nextUntil(".chapter").animate({
                    // height:'toggle',
                    visibility: 'toggle',
                    opacity: 'toggle',
                    // speed: 'fast',
                    easing: 'linear'
                });

            if($target.data('toggle') && $target.nextUntil(".chapter").height()) {
                $target.find(" >.period-show >.es-icon").addClass('es-icon-anonymous-iconfont').removeClass('es-icon-remove');
                $target.data('toggle', false);

            } else {
                $target.find(" >.period-show >.es-icon").addClass('es-icon-remove').removeClass('es-icon-anonymous-iconfont');
                $target.data('toggle', true);
            }
        }
      
    });

    module.exports = chapterAnimate;

    
});