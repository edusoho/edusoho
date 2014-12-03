define(function(require, exports, module) {

    var EditorFactory = require('common/kindeditor-factory');
    var Validator = require('bootstrap.validator');
    require('jquery.sortable');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        var editor = EditorFactory.create('#user_terms_body', 'simple');

            editor.sync();

        var validator = new Validator({
            element: '#auth-form'
        });
        
        validator.addItem({
            element: '[name=temporary_lock_allowed_times]',
            rule: 'integer'
        });

        validator.addItem({
            element: '[name=temporary_lock_hours]',
            rule: 'integer'
        });

        var hideOrShowTimeAndHours = function (){
          if ( $('[name=temporary_lock_enabled]').filter(':checked').attr("value") == 1 ){
            $('#times_and_hours').show();
          }else if ( $('[name=temporary_lock_enabled]').filter(':checked').attr("value") == 0 ){
            $('#times_and_hours').hide();
          };
        };
        hideOrShowTimeAndHours();
        $('[name=temporary_lock_enabled]').change(function (){
           hideOrShowTimeAndHours();
        });  

    	$(".register-list").sortable({
			'distance':20
              });

              $("#show-register-list").hide();

              $("#hide-list-btn").on("click",function(){
                    $("#show-register-list").hide();
                    $("#show-list").show();
              });

              $("#show-list-btn").on("click",function(){
                    $("#show-register-list").show();
                    $("#show-list").hide();
             });
    };

});