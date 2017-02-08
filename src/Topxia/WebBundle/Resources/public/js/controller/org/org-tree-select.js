define(function(require, exports, module) {
    var SelectTree = require('edusoho.selecttree');
    exports.run = function() {
        
        var initOrgOptions = function() {
            var option = {};
            if ($("#modalOrgSelectTree").length > 0) {
                option.element = "#modalOrgSelectTree";
                option.modal = true;
            } else if ($("#orgSelectTree").length > 0) {
                option.element = "#orgSelectTree";
            }
            return option;
        }
        var option = initOrgOptions();

        if (option.element) {
            var selectTree = new SelectTree(option);
        }
    };

});