define(function(require, exports, module) {
    require('../../../../topxiaweb/js/util/qrcode').run();
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var UserSign = require('../../../../topxiaweb/js/util/sign.js');

    exports.run = function() {

        var buy_btn = false;
        
        $('.buy-btn').click(function() {
            if (!buy_btn) {
                $('.buy-btn').addClass('disabled');
                buy_btn = true;
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


        if ($('#classroom-sign').length > 0) {
            var userSign = new UserSign({
            element: '#classroom-sign',
            });
        }

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