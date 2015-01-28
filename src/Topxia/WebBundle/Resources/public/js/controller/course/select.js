define(function(require, exports, module) {


    exports.run = function() {

        $('#sure').on('click',function(){

            $('#sure').button('submiting').addClass('disabled');

            console.log(1);

        });

    };

    
});