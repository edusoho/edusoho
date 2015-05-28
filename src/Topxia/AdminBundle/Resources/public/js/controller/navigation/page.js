define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    require('jquery.sortable');
    exports.run = function() {

        $('tbody').on('click', '.delete-btn', function() {
            if (!confirm('确认要删除此导航吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(response) {
                if (response.status == 'ok') {
                    Notify.success('删除成功!');
                    setTimeout(function(){
                        window.location.reload();
                    }, 500);
                } else {
                    alert('服务器错误!');
                }
            }, 'json');

        });

        var group = $('.navigation-table tbody').sortable({
            group: 'serialization',
            containerPath: '> tr',
            itemSelector: 'tr.has-subItems',
            placeholder: '<tr class="placeholder"/>',
            onDrop: function (item, container, _super) {
                _super(item, container);
                var $tbody = $(item).parent();
                $tbody.find('tr.has-subItems').each(function() {
                    var $tr = $(this);
                    $tbody.find('[data-parent-id=' + $tr.data('id') + ']').detach().insertAfter($tr);
                });
                var data = group.sortable("serialize").get();
                $.post($tbody.data('updateSeqsUrl'), {data:data}, function(response){
                });
            }
        });


    };

});