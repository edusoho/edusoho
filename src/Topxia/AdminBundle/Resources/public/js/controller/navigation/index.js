define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    require('jquery.sortable');
    require('jquery.treegrid');
    exports.run = function() {

        $('#navigation-table').treegrid({
            expanderExpandedClass: 'glyphicon glyphicon-chevron-down',
            expanderCollapsedClass: 'glyphicon glyphicon-chevron-right'
        });

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
            itemSelector: 'tr.has-subItems,tr.child',
            placeholder: '<tr class="placeholder"/>',
            onDrop: function(item, container, _super) {
                var $tbody = $(item).parent();
                $tbody.find('tr.has-subItems').each(function() {
                    var $tr = $(this);
                    $tbody.find('[data-parent-id=' + $tr.data('id') + ']').detach().insertAfter($tr);
                });
                var data = group.sortable("serialize").get();
                var postData = [];
                data.forEach(function(obj) {
                    postData.push({
                        id: obj.id,
                        parentId: obj.parentId
                    });
                });
                $.post($tbody.data('updateSeqsUrl'), {data: postData}, function(response){
                });
                _super(item, container);
            }
        });
    };

});