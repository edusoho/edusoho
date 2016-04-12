define(function(require, exports, module) {

    exports.run = function(options) {
        $('#files').on('click', '.delete', function(){
            if (!confirm(Translator.trans('真的要删除该文件吗？'))) {
                return ;
            }
            $.post($(this).data('url'), function(){
                window.location.reload();
            });
        });
        
    };

});
