define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        $(".detail").popover({
            html: true,
            trigger: 'hover'
        });

        $('#check-result-table').on('click', 'button.install', function() {

            if (!confirm('确认要安装此软件包吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(response) {

                if (response.status == 'ok') {
                    $('#' + $btn.data('target')).remove();
                    Notify.success('安装成功！');
                } else {
                    alert('服务器错误!');
                }

            }, 'json');

        });

        $('#check-result-table').on('click', 'button.upgrade', function() {
            if (!confirm('确认要升级此软件包吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(response) {

                if (response.status == 'ok') {
                    $('#' + $btn.data('target')).remove();
                    Notify.success('升级成功！');
                } else {
                    alert('服务器错误!');
                }

            }, 'json');

        });

    };

});