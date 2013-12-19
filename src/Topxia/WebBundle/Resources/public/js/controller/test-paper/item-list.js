define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var ItemCreator = require('./item-creator');

    exports.run = function() {
        var $container = $('#test-item-container');

        var creator = new ItemCreator({
            element: $container,
        });

        
        require('../../util/batch-delete')($container);
        require('../../util/item-delete')($container);
    };

});