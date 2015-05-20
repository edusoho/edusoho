define(function(require, exports, module) {
    exports.run = function() {

        var join_btn = false;

        $('.join-btn').click(function() {
            if (!join_btn) {
                $('.join-btn').addClass('disabled');
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

    };

});