define(function(require, exports, module) {
    $(".cancel-refund").on('click', function(){
        if (!confirm(Translator.trans('真的要取消退款吗？'))) {
            return false;
        }

        $.post($(this).data('url'), function(){
            window.location.reload();
        });
    });
});