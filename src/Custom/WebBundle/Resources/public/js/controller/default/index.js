define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        $('.panel-collapse').on('hide.bs.collapse', function () {
            $(this).parent('.panel-course').removeClass('active');
        });
        $('.panel-collapse').on('show.bs.collapse', function () {
            $(this).parent('.panel-course').addClass('active');
        });
    };
});