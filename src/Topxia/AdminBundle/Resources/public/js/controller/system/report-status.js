define(function(require, exports, module) {
    exports.run = function() {

        var $table = $('#direcory-check-table');
        $.post($table.data('url'),function(html){
            $table.find('tbody').html(html);
        });
        

    };

});