define(function(require, exports, module) {

    exports.run = function() {

        $('#announcement-table').on('click','.delete-btn',function(){

            $.post($(this).data('url'),function(){

                window.location.reload();

            });
        });

    };

});