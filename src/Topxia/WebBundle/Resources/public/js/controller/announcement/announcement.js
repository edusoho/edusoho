define(function(require, exports, module) {

    exports.run = function() {
        require("../../controller/announcement/announcement-manage").run();
        
        $('.announcement-list').on('click', '[data-role=delete]', function(){
            if (confirm('真的要删除该公告吗？')) {
                $.post($(this).data('url'), function(){
                    window.location.reload();
                });
            }
            return false;
        });

        // $('.model [data-toggle="tooltip"]').tooltip({container: 'body'});
        
        toggle();

        $('.annoucement-add-btn, .es-icon-edit').click(function(){
            $('#modal').modal('hide');
        })
    };

    var toggle = function() {

        if($(".alert-edit").height()) {
            var alertHeader = $(".alert-edit .alert-header");
            var alertIcon = alertHeader.find(".icon-click");

            if(alertIcon.hasClass('es-icon-chevronright') ) {
                alertIcon.data('toggle', true);

            }else {
                alertIcon.data('toggle', false);
            }

            alertHeader.click(function() {
                $(this).siblings(".details").animate({
                    // height:'toggle',
                    visibility: 'toggle',
                    opacity: 'toggle',
                    // speed: 'fast',
                    easing: 'linear'
                });

                var btn = $(this).find(".icon-click");

                if(btn.data('toggle') && btn.parents(".alert-header").siblings(".details").height()) {
                    btn.addClass('es-icon-keyboardarrowdown').removeClass('es-icon-chevronright');
                    btn.data('toggle', false);

                } else {
                    btn.addClass('es-icon-chevronright').removeClass('es-icon-keyboardarrowdown');
                    btn.data('toggle', true);
                }
            });
        };
    }



});