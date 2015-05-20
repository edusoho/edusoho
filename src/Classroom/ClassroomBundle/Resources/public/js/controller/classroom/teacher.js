define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('jquery.sortable');

    exports.run = function() {
        $(".teacher-list-group").sortable({
            'distance':20
        });

        $('#select').on('click',function(){

            var name=$('#teacher-input').val();
            $.post($(this).data('url'),"name="+name,function(html){

                $('.teacher').html(html);
                
            });
        });

        $("#publishSure").on("click",function(){

            $('#publishSure').button('submiting').addClass('disabled');

            $.post($("#publishSure").data("url"), function(html) {

                    $("#modal").modal('hide');
                    window.location.reload();

                }).error(function(){
            });
        });

    };

});