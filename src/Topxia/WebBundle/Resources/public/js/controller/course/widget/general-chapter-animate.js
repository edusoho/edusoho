define(function(require, exports, module) {
    var Widget = require('widget');
    var chapterAnimate = Widget.extend({
        events: {
            'click .js-period-list .js-icon': 'onClickChapter'
        },

        setup: function() {

        },

        onClickChapter: function(e) {
            var $target = $(e.currentTarget).parents('.js-chapter');
            $target.nextUntil(".js-chapter").animate({
                    visibility: 'toggle',
                    opacity: 'toggle',
                    easing: 'linear'
                });

            var $icon = $target.find(".item-actions .js-icon");
            if ($icon.hasClass('glyphicon-chevron-up')) {
                $icon.removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
            } else {
                $icon.removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
            }

            if($target.data('toggle') && $target.nextUntil(".js-chapter").height()) {
                $target.data('toggle', false);

            } else {
                $target.data('toggle', true);
            }
        }
      
    });

    module.exports = chapterAnimate;

    
});