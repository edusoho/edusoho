define(function(require, exports, module) {

    var Widget = require('widget');
    var Notify = require('common/bootstrap-notify');

    var SmsSender = Widget.extend({
        attrs: {
        	validator: 0,
        	url:'',
        	smsType:'',
        	hasMobile: ($("[data-role=mobile]").length > 0),
        	hasNickname:($("[data-role=nickname]").length > 0)
        },
        events: {
        	"click" : "smsSend"
        },
        setup: function() {
            var validator = this.get("validator");
    		var hasMobile = this.get("hasMobile");
    		var hasNickname = this.get("hasNickname");

	    	if (hasMobile){	
	    		validator.addItem({
		            element: '[name="mobile"]',
		            required: true,
		            rule: 'phone'            
		        });
	    	}

	    	if (hasNickname){	
	    		validator.addItem({
		            element: '[name="nickname"]',
		            required: true          
		        });
	    	}

	    	validator.addItem({
	            element: '[name="sms_code"]',
	            required: true,
	            rule: 'integer fixedLength{len:6}',
	            display: '短信验证码'           
	        });

        },
        postData: function(url, smsType, hasNickname) {

	        var refreshTimeLeft = function(){
	        	var leftTime = $('#js-time-left').html();
	        	$('#js-time-left').html(leftTime-1);
	        	if (leftTime-1 > 0) {
	        		setTimeout(refreshTimeLeft, 1000);
	        	} else {
	        		$('#js-time-left').html('');
			        $('#js-fetch-btn-text').html('获取短信验证码');
	        	}
	        };

        	var data = {};	        	
        	data.to = $('[name="mobile"]').val();        	
        	if(hasNickname){
        		data.nickname = $('[name="nickname"]').val();
        	}
        	data.sms_type = smsType;
        	$.post(url,data,function(response){
        		if (("undefined" != typeof response['ACK'])&&(response['ACK']=='ok')) {
	        		$('#js-time-left').html('120');
	        		$('#js-fetch-btn-text').html('秒后重新获取');
	        		Notify.success('发送短信成功');
	        		refreshTimeLeft();
	        	}else{
	        		if ("undefined" != typeof response['error']){
	        			Notify.danger(response['error']);
	        		}else{
	        			Notify.danger('发送短信失败，请联系管理员');
	        		}
	        	}
        	});
        	return this;
        },
        smsSend: function(){
        	var leftTime = $('#js-time-left').html();
    		if (leftTime.length > 0){
    			return false;
    		}

        	var validator = this.get("validator");
    		var hasMobile = this.get("hasMobile");
    		var hasNickname = this.get("hasNickname");
    		var postData = this.postData;
			var url = this.get("url");
    		var smsType = this.get("smsType");

    		var send = function(){
    			if (hasMobile){
					validator.query('[name="mobile"]').execute(function(error, results, element) {
						if (error){
							return false;
						}
					    postData(url, smsType, hasNickname);
					});
				}
    		}
        	
    		if (hasNickname){
    			validator.query('[name="nickname"]').execute(function(error, results, element) {
					if (error){
						return false;
					}
					send();
				});
    		} else {
    			send();
    		}
    		
			return this;
        }

    });

    module.exports = SmsSender;
});