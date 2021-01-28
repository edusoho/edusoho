define(function(require, exports, module) {

    require('jquery.select2');
    require('jquery.select2-css');

    exports.run = function(options) {

        $('.category-select, [data-role="tree-select"], [name="categoryId"]').select2({
            treeview: true,
            dropdownAutoWidth: true,
            treeviewInitState: 'collapsed',
            placeholderOption: 'first'
        });
    };

});
