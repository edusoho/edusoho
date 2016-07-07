define(function(require, exports, module) {
    exports.run = function() {
        $('#announcement-table').on('click', '.delete-btn', function() {

            if (!confirm(Translator.trans('确定删除此公告吗？'))) {
                return;
            }

            $.post($(this).data('url'), function() {

                window.location.reload();

            });
        });

    };

});