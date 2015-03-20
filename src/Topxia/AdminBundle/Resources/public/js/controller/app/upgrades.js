define(function(require, exports, module) {

    exports.run = function() {

        $(".description-more")
            .popover({
                html: true,
                placement: 'bottom',
                trigger: 'hover'
            })
            .click(function(e) {
                e.preventDefault()
            });

    };

});