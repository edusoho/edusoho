define(function(require, exports, module) {
	require('jquery.sortable');

	exports.run = function() {

		var $list = $('.tbady-category').sortable({
		    containerSelector: 'table',
		    itemPath: '> tbody',
		    itemSelector: '.catgory-tr',
		    placeholder: '<tr class="placeholder"/>',
		    onDrop: function (item, container, _super) {
                _super(item, container);
                var data = $list.sortable("serialize").get();

                $.post($list.data('sortUrl'), {ids:data}, function(response){
                    $list.find('.catgory-tr').each(function(index){
                        $(this).find('.number').text(index+1);
                    });

                });
            },
		    serialize: function(parent, children, isContainer) {
                return isContainer ? children : parent.attr('id');
            }
		})

		$('body').on('click','.delete-category', function() {
            if (!confirm('真的要删除该分类吗？')) {
                return ;
            }

            var $item = $('#'+$(this).data('id'));
            $.post($(this).data('url'), function(html) {
                $item.remove();
            });

        });

        $('.category-collapse').collapse();
	}
});