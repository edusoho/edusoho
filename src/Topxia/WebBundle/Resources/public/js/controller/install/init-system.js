define(function(require, exports, module) {

    exports.run = function() {

        $("#init-system").on('click', function(){
           $(this).addClass("disabled").text("正在初始化系统...");   
        });

    };

});