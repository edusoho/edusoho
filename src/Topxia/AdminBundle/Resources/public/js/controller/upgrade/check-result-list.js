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
               console.log(response);
            }, 'json');

        });

	};

});