define(function(require, exports, module) {

    var AutoComplete = require('autocomplete');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var validator = new Validator({
            element: '#transfer-group-form',
            autoSubmit: false,
            onFormValidated: function(error){
                    if (error) {
                    return false;
                }
               $('#nickname').attr('value',$('#username').val());
               $('#myModal').modal('show');
            }
        });

        validator.addItem({
            element: '[name="user[name]"]',
            required: true,
            rule: 'remote'
        });



    };

});