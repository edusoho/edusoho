define(function(require, exports, module) {

    exports.run = function() {

        $('#deadlineAlert').on('click', function() {
            document.cookie = " deadlineAlert= " + escape("closed");
        });
    };

});