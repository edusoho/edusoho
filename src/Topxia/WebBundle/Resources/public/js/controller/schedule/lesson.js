define(function(require, exports, module) {
    require('jquery.sortable');
    var Schedule = require('/bundles/topxiaweb/js/controller/widget/schedule.js');
    exports.run = function() {
        var schedule = new Schedule({
            element: 'body',
        });
    }
});
