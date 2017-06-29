define(function(require, exports, module) {
    "use strict";
    var Lazyload = require('echo.js');

    exports.run = function() {
        Lazyload.init();
    };
});