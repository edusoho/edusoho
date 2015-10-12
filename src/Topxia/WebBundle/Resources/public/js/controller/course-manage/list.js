define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    require("$");
    exports.run = function() {
        require('./header').run();
        var $container = $('#quiz-table-container');
        require('./header').run();
        require('../../util/short-long-text')($container);
        require('../../util/batch-select')($container);
        require('../../util/batch-delete')($container);
        require('../../util/item-delete')($container);
    };

});