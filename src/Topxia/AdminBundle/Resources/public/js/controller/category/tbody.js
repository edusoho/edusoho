define(function(require, exports, module) {

    // var Cookie = require('cookie');

    require('jquery.sortable');

    exports.run = function() {

        $('.list-table .td.name>i').click(function() {
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

        $('#category-table-body>ul').sortable({
            distance: 20,
            isValidTarget: function ($item, container) {
                var $targetContainerItems = $(container.items).not('.placeholder');
                if ($targetContainerItems.length > 0) {
                    if ($targetContainerItems.data('parentId') == $item.data('parentId')) {
                        return true;
                    }
                }
                return false;
            },
            onDrop: function($item, container, _super) {
                var sortedItems = container.el.find('>li');
                var ids = [];
                sortedItems.each(function(i) {
                    var $item = $(sortedItems.get(i));
                    ids.push($item.data('id'));
                    $item.find('>.row>.weight').text(i+1);
                });

                $.post($('#category-table-body').data('sortUrl'), {ids:ids}, function(response){
                    
                });
                _super($item, container);
            }//,
            // serialize: function(parent, children, isContainer) {
            //     console.log('parent',parent);
            //     console.log('children',children);
            //     console.log('isContainer',isContainer);
            //     console.log('--->');
            //     return isContainer ? children.join() : parent.text();
            // }
        });

    };
});