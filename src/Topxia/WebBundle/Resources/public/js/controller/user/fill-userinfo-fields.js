define(function(require, exports, module) {
    
    var UserInfoFieldsItemValidate = require('./userinfo-fields-common.js');

    exports.run = function() {

        new UserInfoFieldsItemValidate({
            element: '#fill-userinfo-form'
        });

    }

});