define(function(require,exports,module){
    var Notify = require('common/bootstrap-notify');

    $('#essay-table').on('click','[data-role=publish-item]',function(){
        $.post($(this).data('url'),function(){
            Notify.success('操作成功!');
            window.location.reload();
        })
    });

    $('#essay-table').on('click','[data-role=unpublish-item]',function(){
        $.post($(this).data('url'),function(){
            Notify.success('操作成功!');
            window.location.reload();
        })
    });

    $('#essay-table').on('click','[data-role=delete-item]',function(){

        if (!confirm('您真的要删除该课件吗？')) {
            return ;
        };
        $.post($(this).data('url'),{id:$(this).data('id')},function(){
            Notify.success('操作成功!');
            window.location.reload();
        })
    });

    $('[data-role=batch-select-all]').click(function(){
        $('#essay-table').find('[data-role=batch-item]').each(function(index,item){
            if($('[data-role=batch-select-all]').is(':checked')) {
                $(item).prop('checked',true);
            } else {
                $(item).prop('checked',false);
            }
        });
    });

    $('[data-role=batch-delete-all-btn]').click(function(){
        var ids = [];
        $('#essay-table').find('[data-role=batch-item]').each(function(index,item){
            if ($(item).is(':checked')) {
                ids.push($(item).val());
            };
        });

        if (ids.length == 0) {
            Notify.danger('未选中任何文章');
            return ;
        };

        if (!confirm('确认删除选中文章？')) {
            return ;
        };

        $.post($(this).data('url'),{ids:ids},function(){
            Notify.success('操作成功!');
            window.location.reload();
        }).error(function(){
            Notify.danger('删除失败！')
        });
    });
});