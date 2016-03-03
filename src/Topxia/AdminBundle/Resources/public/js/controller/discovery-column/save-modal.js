define(function(require, exports, module) {

    require('jquery.sortable');
    var Sticky = require('sticky');
    var Notify = require('common/bootstrap-notify');
    exports.run = function() {

        var group = $('.table-hover tbody').sortable({
            group: 'serialization',
            containerPath: '> tr',
            itemSelector: 'tr.sort',
            placeholder: '<tr class="placeholder"/>',
            onDrop: function (item, container, _super) {
                _super(item, container);
                var $tbody = $(item).parent();
                // $tbody.find('tr.has-subItems').each(function() {
                //     var $tr = $(this);
                //     $tbody.find('[data-parent-id=' + $tr.data('id') + ']').detach().insertAfter($tr);
                // });
                var data = group.sortable("serialize").get();
                $.post($tbody.data('updateSeqsUrl'), {data:data}, function(response){
                });
            }
        });

        $('.delete-btn').on('click', function() {

            if (!confirm('确定要删除该分类展示吗?')) {
                return ;
            }

            $.post($(this).data('url'), function() {
            	location.reload();
            });

        });

        $('.edit-btn').on('click', function() {
        	$.post($(this).data('url'), function() {

            });
        });
    }
});