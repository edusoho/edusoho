define(function(require, exports, module) {
    
    exports.run = function() {
        $('.tab-body ul').each(function(){
            if ($(this).find('li.active').length == 0) {
                $(this).find('li.all').addClass('active');
            }
        });

    };

    
});