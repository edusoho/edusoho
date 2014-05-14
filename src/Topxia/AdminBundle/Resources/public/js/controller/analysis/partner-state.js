define(function(require, exports, module) {

    
    require('jquery.form');

    exports.run = function() {

        $('#state-table-tr>th').on('click', function() {

           $('#sort').val($(this).data('sort'));

           $('#message-search-form').submit();

        });
    };

});