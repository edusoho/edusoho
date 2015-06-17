define(function(require, exports, module) {

    exports.run = function() {

        $(".description-more")
            .popover({
                html: true,
                placement: 'bottom',
                trigger: 'focus',
                toggle: "popover"
            })
            .click(function(e) {
                e.preventDefault()
            });

    };

});