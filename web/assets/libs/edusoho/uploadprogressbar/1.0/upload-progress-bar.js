define(function(require, exports, module) {

    var Widget = require('widget');

    var UploadProgressBar = Widget.extend({
        attrs: {
        	percentage: 0
        },
        show: function () {
	        this.element.show();
	        return this;
    	},
    	hide: function () {
        	this.element.hide();
        	return this;
    	},
    	setProgress: function (percentage) {
	        this.set("percentage", percentage);
	        this.element.find('.progress-bar').css('width', percentage + '%');
	        return this;
	    },
	    setComplete: function () {
	        this.setProgress(100);
	        return this;
	    },
	    reset: function () {
	        this.setProgress(0);
	        return this;
	    },
	    isProgressing: function () {
	        return this.get("percentage") > 0;
	    }
    });

    module.exports = UploadProgressBar;
});