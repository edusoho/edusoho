define(function(require, exports, module) {
   var SelectTree = require('edusoho.selecttree');
    exports.run = function(options) {
         var selectTree = new SelectTree({
            element: "#orgSelectTree",
            name: 'likeOrgCode'
        });
    }
});