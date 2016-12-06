define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        if($(".alert-warning").length>0){
            $(".search-button").hide();
        }
    }

})