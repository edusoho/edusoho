define(function(require, exports, module) {

    require('jquery.sortable');

    exports.run = function() {

    	$(".buy-userinfo-list").sortable({
			'distance':20
	});

    	if($("[name=buy_fill_userinfo]:checked").val()==1)$("#buy-userinfo-list").show();
    	if($("[name=buy_fill_userinfo]:checked").val()==0)$("#buy-userinfo-list").hide();
    	
    	$("[name=buy_fill_userinfo]").on("click",function(){
	    	if($("[name=buy_fill_userinfo]:checked").val()==1)$("#buy-userinfo-list").show();
	    	if($("[name=buy_fill_userinfo]:checked").val()==0)$("#buy-userinfo-list").hide();
    	});
    	

    };

});