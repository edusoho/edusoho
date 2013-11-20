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
            rule: 'remote email',
            onItemValidated: function(err, msg, ele) {
               
                if(msg=="该Email地址已经被占用了"){
                    
                    $("#email_info").html("该Email地址已经存在 <a href='javascript:void(0)' id='photo_login'>请点击登陆</a>");
                    buidlerLogin();
                    return;
                }else{
                   
                     $("#email_info").html(msg);
                }
            }
            
           
            
        });

        validator.addItem({
            element: '[name="activitymember[nickname]"]',
            required: true,
            rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:14} remote'
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