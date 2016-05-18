define(function(require, exports, module) {
    var ztree = require('edusoho.ztree');
    exports.run = function(options) {
        ztree('#orgZtree', "#orgName", "#orgCode");
    }
});