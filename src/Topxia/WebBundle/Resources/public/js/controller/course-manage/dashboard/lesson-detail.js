define(function(require, exports, module) {
    require('echarts');

    exports.run = function() {
        $('.average-learn-lesson-popover').popover({
            html: true,
            trigger: 'hover',
            placement: 'bottom',
            template: '<div class="popover tata-popover tata-popover-lg" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
            content: function() {
                var html = $(this).siblings('.popover-content').html();
                return html;
            }
        });

        $('.video-popover').popover({
            html: true,
            trigger: 'hover',
            placement: 'bottom',
            template: '<div class="popover tata-popover tata-popover-lg" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
            content: function() {
                var html = $(this).siblings('.popover-content').html();
                return html;
            }
        });

        $('.average-video-popover').popover({
            html: true,
            trigger: 'hover',
            placement: 'bottom',
            template: '<div class="popover tata-popover tata-popover-lg" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
            content: function() {
                var html = $(this).siblings('.popover-content').html();
                return html;
            }
        });

        $('.average-score-popover').popover({
            html: true,
            trigger: 'hover',
            placement: 'bottom',
            template: '<div class="popover tata-popover tata-popover-lg" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
            content: function() {
                var html = $(this).siblings('.popover-content').html();
                return html;
            }
        });
    };
});