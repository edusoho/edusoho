define(function(require, exports, module) {

    exports.run = function() {

        var $panel = $('#material-lib-items-panel');
	    require('../../util/batch-select')($panel);
	    require('../../util/batch-delete')($panel);
	    
	    var $list = $("#material-item-list");
	    
	    $list.on('click', '.delete-material-btn', function(e) {
            if (!confirm('您真的要删除该文件吗？')) {
                return ;
            }
            var $btn = $(e.currentTarget);
            $.post($(this).data('url'), function(response) {
                $btn.parents('.item-material').remove();
                sortList($list);
                Notify.success('文件已删除！');
            }, 'json');
        });
    }

});