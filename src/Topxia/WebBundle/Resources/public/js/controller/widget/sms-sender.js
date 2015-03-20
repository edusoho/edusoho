define(function(require, exports, module) {

    var Widget = require('widget');
    var Notify = require('common/bootstrap-notify');

    var SmsSender = Widget.extend({
        attrs: {
        	validator: 0,
        	url:'',
        	smsType:'',
        	getPostData: function(data){
	        	return data;
        	},
        	preSmsSend: function(){
				return true;
        	}
        },
        events: {
        	"click" : "smsSend"
        },
        setup: function() {

        },
        postData: function(url, data) {

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

			var url = this.get("url");
			var data = {};
	        data.to = $('[name="mobile"]').val();        	
	        data.sms_type = this.get("smsType");

			data = $.extend(data, this.get("getPostData")(data));

			if(this.get("preSmsSend")()) {
				this.postData(url, data);
			}
    		
			return this;
        }

    });

    module.exports = SmsSender;
});