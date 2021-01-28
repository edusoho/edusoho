define(function(require, exports, module) {

    exports.run = function(options) {
        $('#files').on('click', '.delete', function(){
            if (!confirm(Translator.trans('admin.file.delete_hint'))) {
                return ;
            }
            $.post($(this).data('url'), function(){
                window.location.reload();
            });
        });
        
    };

});
