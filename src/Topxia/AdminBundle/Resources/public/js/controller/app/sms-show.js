define(function(require, exports, module) {

    exports.run = function() {
        $(".sms-reason").on('mousemove',function(){
            $(".reason").addClass("over");
        });

        $(".sms-reason").on('mouseout',function(){
            $(".reason").removeClass("over");

        });

    };

});
