define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('jquery.raty');
    require('common/validator-rules').inject(Validator);

    function buidlerLogin(){

        $('#photo_login').unbind('click');
        $('#photo_login').on('click',function(){
            $('.modal.in').modal('hide');

            $("#login-modal").modal('show');
            $.get($('#login-modal').data('url'), function(html){
                $("#login-modal").html(html);
            });
        });
    }

    exports.run = function() {

        var validator = new Validator({
            element: '#ActivityForm'
        });

      
        validator.addItem({
            element: '[name="activitymember[email]"]',
            required: true,
            rule: 'email', 
        });

        validator.addItem({
            element: '[name="activitymember[nickname]"]',
            required: true,
            rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:14}'
        });

        validator.addItem({
            element: '[name="activitymember[mobile]"]',
            required: true,
            rule: 'mobile'
        });

        validator.addItem({
            element: '[name="activitymember[truename]"]',
            required: true,
            rule: 'truename byte_minlength{min:2} byte_maxlength{max:12}'
        });


      

    };

});