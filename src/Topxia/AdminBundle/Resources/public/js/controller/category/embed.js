define(function(require, exports, module) {

    var Cookie = require('cookie');
    require('jquery.treegrid.css');
    require('jquery.treegrid');

    require('jquery.sortable.v0.9.13');

    exports.run = function() {

        $('.list-table .td.first>i').click(function() {
            var $parentNode = $(this).closest('.row');
            console.log($parentNode);
            if ($parentNode.hasClass('row-collapse')) {
                $parentNode.removeClass('row-collapse').addClass('row-expand');
                $(this).removeClass('glyphicon-chevron-right').addClass('glyphicon-chevron-down');
                $parentNode.next('ul.list-table').find('>li').show();
            } else if ($parentNode.hasClass('row-expand')) {
                $parentNode.removeClass('row-expand').addClass('row-collapse');
                $(this).removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-right');
                $parentNode.next('ul.list-table').find('>li').hide();
            }
            
        });

        // $('#category-table').sortable({
        //     delay: 500,
        //     containerSelector: 'table',
        //     itemPath: '> tbody',
        //     itemSelector: 'tr.treegrid-top',
        //     placeholder: '<tr class="placeholder"><td colspan="4"></td></tr>',
        //     onDrop: function ($item, container, _super) {
        //         console.log($('[data-parent-id="' + $item.data('id') + '"]'));
        //         console.log($item);
        //         // $('[data-parent-id="' + $item.data('id') + '"]').after($item);
        //         _super($item, container);
        //     }
        // });

    };
});