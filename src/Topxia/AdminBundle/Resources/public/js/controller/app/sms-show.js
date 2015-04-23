define(function(require, exports, module) {

    exports.run = function() {

        $("#article-property-tips").popover({
            html: true,
            trigger: 'hover',//'hover','click'
            placement: 'right',//'bottom',
            content: $("#article-property-tips-html").html(),
            delay: { "hide": 850 }
        });

    };

});
