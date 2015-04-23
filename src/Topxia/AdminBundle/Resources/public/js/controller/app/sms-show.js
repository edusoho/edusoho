define(function(require, exports, module) {

    exports.run = function() {

        $("#sms-reason-tips").popover({
            html: true,
            trigger: 'click',
            placement: 'right',
            content: $("#sms-reason-tips-html").html(),
        });

    };

});
