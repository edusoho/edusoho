define(function(require, exports, module) {
<<<<<<< HEAD
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var currentNum = $('[name="live_student_capacity"]').data('value');

        var validator = new Validator({
                element: '#course-form'
            });
        
        validator.addItem({
            element: '[name="perLiveMaxStudentNum"]',
            rule: 'integer max{max: '+ currentNum + '}'
        });

=======

    require('jquery.sortable');

    exports.run = function() {

    	$(".buy-userinfo-list").sortable({
			'distance':20
	});

    	if($("[name=buy_fill_userinfo]:checked").val()==1)$("#buy-userinfo-list").hide();
    	if($("[name=buy_fill_userinfo]:checked").val()==0){
                    $("#buy-userinfo-list").hide();
                    $("#show-list").hide();
                }
    	
    	$("[name=buy_fill_userinfo]").on("click",function(){
	    	if($("[name=buy_fill_userinfo]:checked").val()==1){
                                $("#show-list").show();
                                $("#buy-userinfo-list").hide();
                            }
    	              if($("[name=buy_fill_userinfo]:checked").val()==0){
                                $("#buy-userinfo-list").hide();
                                $("#show-list").hide();
                            }
    	});
    	
              $("#hide-list-btn").on("click",function(){
                $("#buy-userinfo-list").hide();
                 $("#show-list").show();
            	});

            	$("#show-list-btn").on("click",function(){
                $("#buy-userinfo-list").show();
                 $("#show-list").hide();
           	 });
>>>>>>> master
    };

});