define(function(require, exports, module) {

    exports.run = function() {
         $('.item-add-btn').on('click',function(){
            $(this).button('loading');
            var $item = $(this).parents('[data-role=item]');
            $.post($(this).data('url'), function(html) {
                $item.remove();
                var type = $(html).attr('data-type');
                $('#questionType-'+type).append(html).find('.empty').remove();
                $item.parents('.modal').modal('hide');
            });
         });

         $('.item-replace-btn').on('click',function(){
            var $btn = $(this);
            var $item = $('#'+$btn.data('replaceid'));
            $btn.button('loading');
            $.post($btn.data('url'), function(html) {
                $btn.parents('.modal').modal('hide');
                $item.replaceWith(html);
            });
     });
    };

});