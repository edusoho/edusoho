define(function(require, exports, module) {
   var SelectZtree = require('edusoho.selectztree');
    exports.run = function(options) {
        var selectTree = new SelectZtree({
            ztreeDom: '#orgZtree',
            clickDom: "#orgName",
            valueDom: "#orgCode"
        });
    }
});