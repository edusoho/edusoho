define(function(require, exports, module){

	require("jquery.bootstrap-datetimepicker");
	require("$");

	exports.run = function(){
		$("#useStartDateTime, #useEndDateTime").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        });

		$("#coupon-search-form").on('click', '.show-data', function(){
			$(this).hide().parent().find('.hide-data').show().end().find('.data').show();
		});	

		$("#lcoupon-search-form").on('click', '.hide-data', function(){
			$(this).hide().parent().find('.show-data').show().end().find('.data').hide();
		});	
	};

});