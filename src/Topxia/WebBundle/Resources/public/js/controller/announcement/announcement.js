define(function(require, exports, module) {

    exports.run = function() {

        $('.announcement-list').on('click', '[data-role=delete]', function(){
            if (confirm('真的要删除该公告吗？')) {
                $.post($(this).data('url'), function(){
                    window.location.reload();
                });
            }
            return false;
        });

        $('[data-toggle="tooltip"]').tooltip();
        
        toggle();

        $('.annoucement-add-btn, .es-icon-edit').click(function(){
            $('#modal').modal('hide');
        })
    };

    var toggle = function() {

        var alertBtn = $(".alert-edit .click>.es-icon");

        if(alertBtn.hasClass('es-icon-chevronright') ) {
            alertBtn.data('toggle', true);
        }else {
            alertBtn.data('toggle', false);
        }

        alertBtn.click(function() {
            
            $(this).parents(".click").siblings(".details").animate({
                // height:'toggle',
                visibility: 'toggle',
                opacity: 'toggle',
                // speed: 'fast',
                easing: 'linear'
            });

            var btn = $(this);

            if(btn.data('toggle') && btn.parents(".click").siblings(".details").height()) {
                btn.addClass('es-icon-keyboardarrowdown').removeClass('es-icon-chevronright');
                btn.data('toggle', false);

            } else {
                btn.addClass('es-icon-chevronright').removeClass('es-icon-keyboardarrowdown');
                btn.data('toggle', true);
            }
        });
    }

});