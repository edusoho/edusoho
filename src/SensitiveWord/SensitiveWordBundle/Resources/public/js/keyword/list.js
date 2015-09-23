define(function(require, exports, module) {

    exports.run = function() {

        $('#keyword-table').on('click','.delete-btn',function(){

            if (!confirm('确定删除此关键字吗？')) {
                return ;
            }

            $.post($(this).data('url'),function(){

                window.location.reload();

            });
        });

    };

});