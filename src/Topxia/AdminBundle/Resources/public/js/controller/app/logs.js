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

        $.post('/admin/app/upgrades_count', function(count){

            if (count > 0) {

                $('.app-upgrade').append("<span class=\"badge mls\" style=\"background-color:#FF3333\">"+count+"</span>");
            }

        });

    };

});