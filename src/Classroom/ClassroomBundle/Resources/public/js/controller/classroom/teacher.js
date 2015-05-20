define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('jquery.sortable');
    var AutoComplete = require('autocomplete');

    exports.run = function() {
        $(".teacher-list-group").sortable({
            'distance':20
        });

        var autocomplete = new AutoComplete({
            trigger: '#teacher-input',
            dataSource: $("#teacher-input").data('url'),
            filter: {
                name: 'stringMatch',
                options: {
                    key: 'nickname'
                }
            },
            selectFirst: true
        }).render();

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