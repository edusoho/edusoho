define(function(require, exports, module) {
    
    Test = require('./util/util');

    exports.run = function() {

        $('.item-add-btn').on('click',function(){
            var $hiddenBody = $('#test-item-table [data-role=item-body]');
            var $item = $(this).parents('[data-role=item]');
            $(this).button('loading');

            $.post($(this).data('url'), function(html) {

                $hiddenBody.append(html);

                var $type = $hiddenBody.find('.questionType');

                $('#questionType-'+$type.data('type')).append($type).find('.empty').remove();

                if($type.data('type') == 'material'){
                    var $html = $hiddenBody.find('[data-type='+$type.attr('id')+']');
                    $('#'+$type.attr('id')).after($html);
                }

                $item.remove();

                if($item.data('type') == 'material'){
                    $('#test-item-table').find('[data-type='+$item.attr('id')+']').remove();
                }

                Test.util();
            });
         });


        $('.item-replace-btn').on('click','',function(){
            var $btn = $(this);
            var $hiddenBody = $('#test-item-table [data-role=item-body]');
            var $item = $('#'+$btn.attr('data-replaceId'));

            $btn.button('loading');

            $.post($btn.data('url'), function(html) {

                $hiddenBody.append(html);
                var $type = $hiddenBody.find('.questionType');
                
                if($item.data('type') == 'material'){
                    $('#test-item-table').find('[data-type='+$item.attr('id')+']').remove();
                }

                $item.replaceWith($type);
                
                if($type.data('type') == 'material'){
                    var $html = $hiddenBody.find('[data-type='+$type.attr('id')+']');
                    $('#'+$type.attr('id')).after($html);
                }

                $btn.parents('.modal').modal('hide');

                Test.util();
            });
        });


    };

});