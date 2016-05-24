define(function(require, exports, module) {

    var Cookie = require('cookie');

    require('jquery.sortable.v0.9.13');

    exports.run = function() {

        $('.list-table .td.first>i').click(function() {
            var $parentNode = $(this).closest('.row');
            if ($parentNode.hasClass('row-collapse')) {
                $parentNode.removeClass('row-collapse').addClass('row-expand');
                $(this).removeClass('glyphicon-chevron-right').addClass('glyphicon-chevron-down');
                $parentNode.next('ul.list-table').find('>li').slideDown();
            } else if ($parentNode.hasClass('row-expand')) {
                $parentNode.removeClass('row-expand').addClass('row-collapse');
                $(this).removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-right');
                $parentNode.next('ul.list-table').find('>li').slideUp();
            }
        });

        var group = $('#category-table-body>ul').sortable({
            distance: 20,
            isValidTarget: function ($item, container) {
                if (container.items.length > 0) {
                    if ($(container.items[0]).data('parentId') == $item.data('parentId')) {
                        return true;
                    }
                }
                return false;
            },
            onDrop: function($item, container, _super) {
                console.log(group.sortable("serialize").get());
                _super($item, container);
            }
        });

    };
});