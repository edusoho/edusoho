define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');

    exports.run = function() {

        $("#sure").on("click",function(){

            $('#sure').button('submiting').addClass('disabled');

            $.post($("#sure").data("url"), function(html) {

                    $("#modal").modal('hide');
                    window.location.reload();

                }).error(function(){
            });
        });
    };

});