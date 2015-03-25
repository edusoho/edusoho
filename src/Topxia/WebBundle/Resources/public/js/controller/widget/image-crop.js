define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    require("jquery.jcrop-css");
    require("jquery.jcrop");

    var ImageCrop = Widget.extend({
    	attrs: {
    		cropedWidth: 480,
	        cropedHeight: 270,
        },

        events: {
            
        },

    	setup: function(){
	    	var self = this;
	    	var $picture = this.element;

	        var scaledWidth = $picture.attr('width'),
	            scaledHeight = $picture.attr('height'),
	            naturalWidth = $picture.data('naturalWidth'),
	            naturalHeight = $picture.data('naturalHeight'),
	            cropedWidth = this.get("cropedWidth"),
	            cropedHeight = this.get("cropedHeight"),
	            ratio = cropedWidth / cropedHeight,
	            selectWidth = (cropedWidth*3/4) * (naturalWidth/scaledWidth),
	            selectHeight = (cropedHeight*3/4) * (naturalHeight/scaledHeight);

	        $picture.Jcrop({
	            trueSize: [naturalWidth, naturalHeight],
	            setSelect: [0, 0, selectWidth, selectHeight],
	            aspectRatio: ratio,
	            onSelect: function(c) {
	                self.trigger("select", c);
	            }
	        });
        }

    });

	module.exports = ImageCrop;
});