define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        $("a[rel=popover]")
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