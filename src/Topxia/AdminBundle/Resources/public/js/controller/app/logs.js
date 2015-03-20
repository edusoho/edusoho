define(function(require, exports, module) {

    exports.run = function() {

        $(".log-message-btn")
            .popover({
                html: true,
                placement: 'left',
                trigger: 'hover'
            })
            .click(function(e) {
                e.preventDefault()
            });

    };

});