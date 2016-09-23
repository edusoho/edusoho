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
                var data = group.sortable("serialize").get();
                    $.post($tbody.data('updateSeqsUrl'), {data:data}, function(response){
                });
            }
        });

        $('tbody').on('click', '.delete-btn', function() {
            if (!confirm(Translator.trans('确定要删除该分类展示吗？'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(response) {
                if (response.status == 'ok') {
                    Notify.success(Translator.trans('删除成功!'));
                    setTimeout(function(){
                        window.location.reload();
                    }, 500);
                } else {
                    alert(Translator.trans('服务器错误!'));
                }
            }, 'json');
        });
        

    }
});