define(function(require, exports, module) {
    exports.run = function() {
        $('[data-toggle="popover"]').popover();
        var $table = $('#direcory-check-table');
        $.post($table.data('url'), function(html) {
            $table.find('tbody').html(html);
        });

        $.post($('.js-ucenter-check').data('url'), function(resp) {
            var $td = $('.js-ucenter-check').closest('td');
            if (resp.status) {
                $td.html('<span class="text-success">√ ' + resp.message + '</span>');
            } else {
                $td.html('<span class="text-danger">X ' + resp.message + '</span>');
            }

        });

        $.post($('.js-email-send-check').data('url'), function(resp) {
            var $td = $('.js-email-send-check').closest('td');
            if (resp.status) {
                $td.html('<span class="text-success">√ ' + resp.message + '</span>');
            } else {
                $td.html('<span class="text-danger">X ' + resp.message + '</span>');
            }

        });
    };

});