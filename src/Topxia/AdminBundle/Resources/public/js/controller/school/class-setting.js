define(function(require, exports, module) {
    exports.run = function() {
        var classList=$(".class-list");
        classList.find('.class-grid').each(function(){
            $(this).hover(
                function(){
                    $(this).find('.class-option').show();
                },
                function(){
                    $(this).find('.class-option').hide();
                }
            );
        });       
    };
});