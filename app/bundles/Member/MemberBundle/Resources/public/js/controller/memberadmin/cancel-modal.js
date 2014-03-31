define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');


    exports.run = function() {

        var $modal = $('#member-cancel-form').parents('.modal');
        var $table = $('#member-table');

        $('#member-table').on('click', '.delete-member', function(){

            if(!confirm('真的要取消该会员吗?')){
                return ;
            }

            $tr = $(this).parents('tr');

            $.post($(this).data('url'),function(){
                $tr.remove();
            });
            var member_count = $('.member_count font'),
                member_count_text = $('.member_count font').text();
            member_count.text(member_count_text-1);
        });

	};

});