define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('jquery.sortable');
    var DynamicCollection = require('../../../../topxiaweb/js/controller/widget/dynamic-collection4');

    exports.run = function() {
        var dynamicCollection = new DynamicCollection({
            element: '#teachers-form-group',
            onlyAddItemWithModel: true
        });

        $(".teacher-list-group").sortable({
            'distance':20
        });

        $('#select').on('click',function(){

            var name=$('#teacher-input').val();
            $.post($(this).data('url'),"name="+name,function(html){

                $('.teacher').html(html);
                
            });
        });
    };

});