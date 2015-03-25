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

        var indentation = '<span class="indentation">&nbsp;&nbsp;&nbsp;&nbsp; └─</span>';
        var navigation = $('.navigation-table tbody');
        var group = $('.navigation-table tbody').sortable({
            group: 'serialization',
            containerPath: '> tr',
            itemSelector: 'tr',
            placeholder: '<tr class="placeholder"/>',
            onDrop: function (item, container, _super) {
                _super(item, container);
                refreshSeq(item);

            }
        });

        function refreshSeq(item) {
            var $prev = $(item).prev();
            if($prev.length > 0) {

            } else {
                if($(item).data('parentId') > 0) {
                    $(item).data('parentId', 0);
                    $(item).find('.indentation').remove();
                }
            }
        }

    };

});