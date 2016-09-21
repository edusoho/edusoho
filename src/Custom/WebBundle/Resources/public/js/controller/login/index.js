define(function(require, exports, module) {
    exports.run = function() {
        $('.remember-me').on('click',function(){
            if( $(this).hasClass('glyphicon-ok') ){
                $(this).removeClass('glyphicon-ok');
                $('#remember').removeAttr('checked');
            }
            else {
                $(this).addClass('glyphicon-ok');
                $('#remember').attr('checked', 'checked');
            }
        });
    };
});