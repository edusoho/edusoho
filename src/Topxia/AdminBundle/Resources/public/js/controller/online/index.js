define(function(require, exports, module) {
    exports.run = function() {
        $('#online-type').click(function () {
            $('#message-search-form').submit();
        })
    }
})