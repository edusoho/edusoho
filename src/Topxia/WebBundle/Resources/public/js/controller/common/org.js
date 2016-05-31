define(function(require, exports, module) {
    var SelectTree = require('edusoho.selecttree');
    exports.run = function() {
        var selectTree = new SelectTree({
            element: "#orgSelectTree",
        });
    };

});