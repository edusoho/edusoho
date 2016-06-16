define(function(require, exports, module) {

    exports.run = function() {
        $form = $('#material-delete-form');

        $('.material-delete-form-btn').click(function(){
            $(this).button('loading').addClass('disabled');
            
            var ids = [];
            $('[data-role=batch-item]:checked').each(function(){
                ids.push(this.value);
            })

            var isDeleteFile = $form.find('input[name="isDeleteFile"]:checked').val();
            $.post($form.attr('action'), {ids:ids, isDeleteFile:isDeleteFile}, function(){
                window.location.reload();
            });
        })
    };

});