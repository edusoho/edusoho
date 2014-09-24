define(function(require, exports, module) {
    var UserSign = require('/bundles/topxiaweb/js/controller/widget/user-sign.js');

     exports.run = function() {
        var userSign = new UserSign({
        element: '#class-sign',
        });
    }
});
