define(function(require, exports, module) {

    exports.run = function() {

        $(".log-message-btn")
            .popover({
                html: true,
                placement: 'left',
                trigger: 'hover'
            })
            .click(function(e) {
                e.preventDefault()
        });
        let $url = $('.js-table').data('url');
        $.post($url, function(count){

            if (count > 0) {

                $('.app-upgrade').append("<span class=\"badge mls\" style=\"background-color:#FF3333\">"+count+"</span>");
            }

        });

    };

});