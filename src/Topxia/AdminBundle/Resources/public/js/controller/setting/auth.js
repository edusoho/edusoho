define(function(require, exports, module) {

    var EditorFactory = require('common/kindeditor-factory');
    var Validator = require('bootstrap.validator');
    require('jquery.sortable');

    exports.run = function() {

        var editor = EditorFactory.create('#user_terms_body', 'simple');

            editor.sync();

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