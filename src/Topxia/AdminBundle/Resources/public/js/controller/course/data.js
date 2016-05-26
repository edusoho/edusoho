define(function(require, exports, module) {
    var SelectTree = require('edusoho.selecttree');
    exports.run = function(options) {
        if ($("#orgSelectTree").val()) {
            var selectTree = new SelectTree({
                element: "#orgSelectTree",
                name: 'likeOrgCode'
            });
        }
    }
});