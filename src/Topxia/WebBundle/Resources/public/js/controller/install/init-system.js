define(function(require, exports, module) {

    exports.run = function() {

        $("#init-system").on('click', function(){
           $(this).addClass("disabled").text(Translator.trans('正在初始化系统...'));   
        });

        $('#upload_mode').on('change', function(){
                if($(this).is(':checked')){
                    $('.cloud-storage').show();
                } else {
                    $('.cloud-storage').hide();
                }

        });

    };

});