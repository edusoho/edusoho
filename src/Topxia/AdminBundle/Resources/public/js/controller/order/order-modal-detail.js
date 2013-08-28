define(function(require, exports, module) {

    exports.run = function() {

        $('#modal').on('click', '.order-detail-tab', function(){
            $(this).addClass('active');
            $('#modal').find('.order-log-tab').removeClass('active');
            $('#modal').find('.order-detail').show();
            $('#modal').find('.order-logs').hide();
        });

         $('#modal').on('click', '.order-log-tab', function(){
            $(this).addClass('active');
            $('#modal').find('.order-detail-tab').removeClass('active');
            $('#modal').find('.order-logs').show();
            $('#modal').find('.order-detail').hide();
        });

    };

});