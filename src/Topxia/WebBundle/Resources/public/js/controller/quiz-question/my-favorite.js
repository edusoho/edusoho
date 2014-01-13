define(function(require, exports, module) {

    exports.run = function() {

        $('body').on('click', '.showQuestion', function(){
            $(this).parent().find('.panel').toggle();
        });

    }


});

