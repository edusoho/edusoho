define(function (require, exports, module) {
    "use strict";

    var Notify = require('common/bootstrap-notify');

    exports.run = function () {
        var ztree = require('edusoho.ztree');
        ztree('#modal-orgZtree', "#modal-orgName", "#modal-orgCode", "modal-ztreeContent");
    }
});
