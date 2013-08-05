define(function(require, exports, module) {

    exports.run = function() {
        $('body').on('click', 'button.delete-btn', function() {
            if (!confirm('确认要删除此导航吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(response) {
                if (response.status == 'ok') {
                    $('#' + $btn.data('target')).remove();
                    toastr.success('删除成功!');
                } else {
                    alert('服务器错误!');
                }
            }, 'json');
        });
    };

});