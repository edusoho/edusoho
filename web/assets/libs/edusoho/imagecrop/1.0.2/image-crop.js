define(function(require, exports, module) {
    var Widget = require('widget');
    require("jquery.jcrop-css");
    require("jquery.jcrop");

    var ImageCrop = Widget.extend({
    	attrs: {
    		group: 'default'
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
	            selectWidth = (cropedWidth) * (naturalWidth/scaledWidth),
	            selectHeight = (cropedHeight) * (naturalHeight/scaledHeight);
            /*$picture.css('height', scaledHeight);
*/
	        var img = $.Jcrop($picture, {
	            trueSize: [naturalWidth, naturalHeight],
	            setSelect: [0, 0, selectWidth, selectHeight],
	            aspectRatio: ratio,
                keySupport: false,
                allowSelect: false,
                onSelect: function(c) {
	                self.trigger("select", c);
	            }
	        });
	        self.set("img", img);
        },

        crop: function(postData){
            var self = this;
        	var cropImgUrl = app.imgCropUrl;
        	if(!postData) {
        		postData = {};
        	}

        	postData = $.extend(this.get("img").tellScaled(), postData, {width: this.element.width(), height: this.element.height(), group: self.get('group')});
            $.post(cropImgUrl, postData ,function(response){
                self.trigger("afterCrop", response);
            })
        }

    });

	module.exports = ImageCrop;
});