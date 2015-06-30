define(function(require, exports, module) {

    exports.run = function() {

        $(".check ").on('click',  function() {
            $(this).siblings().find('.icon').addClass('hide');
            $(this).find('.icon').removeClass('hide');
            $("input[name='payment']").val($(this).attr("id"));
        });
    };

});