define(function(require, exports, module) {

    var showMessage = function(type, message, duration) {
        var $exist = $('.bootstrap-notify-bar');
        if ($exist.length > 0) {
            $exist.remove();
        }

        var html = '<div class="alert alert-' + type + ' bootstrap-notify-bar" style="display:none;">'
        html += '<button type="button" class="close" data-dismiss="alert">×</button>';
        html += message;
        html += '</div>';

        var $html = $(html);
        $html.appendTo('body');

        $html.slideDown(100, function(){
            duration = $.type(duration) == 'undefined' ? 3 :  duration;
            if (duration > 0) {
                setTimeout(function(){
                    $html.remove();
                }, duration * 1000);
            }
        });

    }

    var Notify = {
        primary: function(message, duration) {
            showMessage('primary', message, duration);
        },

        success: function(message, duration) {
            showMessage('success', message, duration);
        },

        warning: function(message, duration) {
            showMessage('warning', message, duration);
        },

        danger: function(message, duration) {
            showMessage('danger', message, duration);
        },

        info: function(message, duration) {
            showMessage('info', message, duration);
        }
    };

    module.exports = Notify;
});
