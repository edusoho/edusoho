define(function (require, exports, module) {
    "use strict";
    exports.run = function () {
        var Importer = require('./importer/importer');
        var id = '#importer-app';
        var importer = new Importer({
            element: "#importer-app",
            type: $(id).data('type'),
            template: $('#importer-template').html()
        });
    }
});
