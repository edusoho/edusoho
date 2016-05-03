define(function(require, exports, module) {
    require('../../../../topxiaweb/js/util/qrcode').run();
    exports.run = function() {

        var join_btn = false;

        $('.btn-lg').click(function() {
            if (!join_btn) {
                $('.btn-lg').addClass('disabled');
                join_btn = true;
            }
            return true;
        });

        $(".cancel-refund").on('click', function(){
            if (!confirm('真的要取消退款吗？')) {
                return false;
            }

            $.post($(this).data('url'), function(){
                window.location.reload();
            });
        });

        $("#quit").on('click', function(){
            if (!confirm('确定退出班级吗？')) {
                return false;
            }

            $.post($(this).data('url'), function(){
                window.location.reload();
            });
        });

        $("#free-join-button").tooltip();

        if ($('.icon-vip').length > 0) {
           $(".icon-vip").popover({
                trigger: 'manual',
                placement: 'auto top',
                html: 'true',
                container: 'body',
                animation: false
            }).on("mouseenter", function () {
                var _this = $(this);
                _this.popover("show");
                $(".popover").on("mouseleave", function () {
                    $(_this).popover('hide');
                });
            }).on("mouseleave", function () {
                var _this = $(this);
                setTimeout(function () {
                    if (!$(".popover:hover").length) {
                        _this.popover("hide")
                    }
                }, 100);
            }); 
        }

    };

});