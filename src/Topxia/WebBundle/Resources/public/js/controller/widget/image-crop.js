define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    require("jquery.jcrop-css");
    require("jquery.jcrop");

    var ImageCrop = Widget.extend({
    	attrs: {
    		cropedWidth: 480,
	        cropedHeight: 270,
	        x: null,
	        x: null,
	        width: null,
	        height: null
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
	            cropedWidth = 480,
	            cropedHeight = 270,
	            ratio = cropedWidth / cropedHeight,
	            selectWidth = 360 * (naturalWidth/scaledWidth),
	            selectHeight = 202.5 * (naturalHeight/scaledHeight);

	        $picture.Jcrop({
	            trueSize: [naturalWidth, naturalHeight],
	            setSelect: [0, 0, selectWidth, selectHeight],
	            aspectRatio: ratio,
	            onSelect: function(c) {
	                self.get('x').val(c.x);
	                self.get('y').val(c.y);
	                self.get('width').val(c.w);
	                self.get('height').val(c.h);
	            }
	        });
        }

    });

	module.exports = ImageCrop;
});