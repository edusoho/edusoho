define(function(require, exports, module) {
    exports.run = function() {
        $('#radio-buttons label').click(function () {
            $(this).find('input').attr('checked', 'checked');
            $('#message-search-form').submit();
        })
    }
})