define(function(require, exports, module) {

    exports.run = function() {

        var $element = $('#app-table-container');

        require('../../util/short-long-text')($element);
        

    };

});