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
            if (!confirm(Translator.trans('site.delete.confirm_hint'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(response) {
                if (response.status == 'ok') {
                    Notify.success(Translator.trans('site.delete_success_hint'));
                    setTimeout(function(){
                        window.location.reload();
                    }, 500);
                } else {
                    alert(Translator.trans('site.service_error_hint'));
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

        $('.navigation-table tbody button').on('mousedown', function(e) {
           e.stopPropagation();
        })
    };

});