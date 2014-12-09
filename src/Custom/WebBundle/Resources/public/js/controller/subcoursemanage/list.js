define(function(require, exports, module) {
    require('jquery.sortable');
    exports.run = function() {

        $('.sub-course-media').on('click', '.delete-btn', function() {
            var $li = $(this).parents('media');
            $.post($(this).data('url'), function(){
                window.location.reload();
            });
        });

        var sortList = function($list) {
            var data = $list.sortable("serialize").get();
            $.post($list.data('sortUrl'), {ids:data}, function(response){
                
            });
        };

        var $list = $('.sort-ul').sortable({
            onDrop: function (item, container, _super) {
                _super(item, container);
                sortList($list);
            },
            serialize: function(parent, children, isContainer) {
                return isContainer ? children : parent.attr('id');
            }
        });

        $('.course-publish-btn').click(function() {
            if (!confirm('您真的要发布该课程吗？')) {
                return ;
            }

            $.post($(this).data('url'), function() {
                window.location.reload();
            });

        });

    };

});