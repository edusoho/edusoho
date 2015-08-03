define(function(require, exports, module){

    exports.run = function() {

        $(".order-pay .check ").on('click',  function() {
            $(this).addClass('active').siblings().removeClass('active').find('.icon').addClass('hide');
            $(this).find('.icon').removeClass('hide');
            $("input[name='payment']").val($(this).attr("id"));
        });
    };

});