define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('jquery.sortable');
    
    exports.run = function() {
        $(".course-list-group").sortable({
            'distance':20
        });
    };

});