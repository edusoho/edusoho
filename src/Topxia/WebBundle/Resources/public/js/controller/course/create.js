define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var SelectTree = require('edusoho.selecttree');
    exports.run = function() {
        if ($("#orgSelectTree").val()) {
            var selectTree = new SelectTree({
                element: "#orgSelectTree",
                name: 'orgCode'
            });
        }

        if ($("#course-create-form").length > 0) {
            var validator = new Validator({
                element: '#course-create-form',
                triggerType: 'change',
                onFormValidated: function(error) {
                    if (error) {
                        return false;
                    }
                    $('#course-create-btn').button('submiting').addClass('disabled');
                }
            });

            validator.addItem({
                element: '[name="title"]',
                required: true
            });

        }
    };

});