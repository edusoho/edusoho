define(function(require, exports, module) {

    var Widget = require('widget');

    var SmsSender = Widget.extend({
        attrs: {
        	validator: 0,
        	smsType:'',
        	hasMobile:false
        },
    	setValidator: function (validator) {
    		this.set("validator", validator);
    		return this;
    	},
    	setSmsType: function (smsType) {
    		this.set("smsType", smsType);
    		return this;
    	},
    	sethasMobile: function (hasMobile) {
    		this.set("hasMobile", hasMobile);
    		return this;
    	},
    	takeEffect: function () {
    		validator = this.get("validator");
    		smsType = this.get("smsType");
    		hasMobile = this.get("hasMobile");

	    	if (hasMobile){	
	    		validator.addItem({
		            element: '[name="mobile"]',
		            required: true,
		            rule: 'phone'            
		        });
	    	}

	    	validator.addItem({
	            element: '[name="sms_code"]',
	            required: true,
	            rule: 'integer constLength{len:6}'            
	        });

	    	refreshTimeLeft = function() { 
	        	var leftTime = $('#js-time-left').html();
	        	$('#js-time-left').html(leftTime-1);
	        	if (leftTime-1 > 0) {
	        		setTimeout("refreshTimeLeft()", 1000);
	        	}else{
	        		$('#js-time-left').html('');
			        $('#js-fetch-btn-text').html('获取短信验证码');
	        	}
	        }

	        postData = function(){
	        	var url = $('.js-sms-send').data('url');
	        	var data = {};
	        	if(hasMobile){
	        		data.to = $('[name="mobile"]').val();
	        	}
	        	data.sms_type = smsType;
	        	$.post(url,data,function(response){
	        		console.log(response);
	        		if (("undefined" != typeof response['ACK'])&&(response['ACK']=='ok')) {
		        		$('#js-time-left').html('120');
		        		$('#js-fetch-btn-text').html('秒后重新获取');
		        		refreshTimeLeft();
		        	}
	        	});
	        }

	        $('.js-sms-send').click(function() {
	        		var leftTime = $('#js-time-left').html();
	        		if (leftTime.length > 0){
	        			return false;
	        		}
	        		if (hasMobile){
						validator.query('[name="mobile"]').execute(function(error, results, element) {
							if (error){
								return false;
							}
						    postData();
						});
					}else{
						postData();
					}

	        });

	        return this;
    	}
    });

    module.exports = SmsSender;
});