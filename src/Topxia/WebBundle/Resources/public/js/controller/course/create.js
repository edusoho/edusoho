define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var SelectZtree = require('edusoho.selectztree');
    exports.run = function() {

        var selectTree = new SelectZtree({
            ztreeDom: '#orgZtree',
            clickDom: "#orgName",
            valueDom: "#orgCode"
        });

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