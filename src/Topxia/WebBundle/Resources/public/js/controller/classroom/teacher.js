define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('jquery.sortable');
    var AutoComplete = require('autocomplete');
    var DynamicCollection = require('../widget/dynamic-collection4');

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

    };

});