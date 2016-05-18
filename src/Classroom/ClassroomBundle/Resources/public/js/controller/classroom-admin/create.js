define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
   var SelectZtree = require('edusoho.selectztree');
    exports.run = function() {
        var selectTree = new SelectZtree({
            ztreeDom: '#orgZtree',
            clickDom: "#orgName",
            valueDom: "#orgCode"
        });
        var validator = new Validator({
            element: '#classroom-create-form',
            onFormValidated: function(error) {
                if (error) {
                    return false;
                }
                $('#classroom-create-btn').button('submiting').addClass('disabled');
            }
        });

        validator.addItem({
            element: '[name="title"]',
            required: true,
            rule: 'minlength{min:2} maxlength{max:30}',
            errormessageUrl: '长度为2-30位'
        });

    };

});