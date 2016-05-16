define(function(require, exports, module) {
    exports.run = function() {

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

        $.ajax({
                url: $('.js-email-send-check').data('url'),
                timeout: 3000
            }).done(function(resp) {
                if (resp.status) {
                    $('.js-email-send-check').closest('td').html('<span class="text-success">√ ' + resp.message + '</span>');
                } else {
                    $('.js-email-send-check').closest('td').html('<span class="text-danger">X ' + resp.message + '</span>');
                }
            })
            .fail(function(resp) {
                $('.js-email-send-check').closest('td').html('<span class="text-danger">X 邮件发送异常 </span>');
            });
    };

});