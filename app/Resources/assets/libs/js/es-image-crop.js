import 'jquery-jcrop/js/jquery.Jcrop.js';
// import 'jquery-jcrop/css/Jcrop.gif';

class EsImageCrop {
	constructor(config) {
		let self = this;
		this.config = $.extend({
			element: null,
			group: 'default'
		}, config);

		let $picture = this.element = $(this.config.element);
		let scaledWidth = $picture.attr('width'),
            scaledHeight = $picture.attr('height'),
            naturalWidth = $picture.data('naturalWidth'),
            naturalHeight = $picture.data('naturalHeight'),
            cropedWidth = this.config.cropedWidth,
            cropedHeight = this.config.cropedHeight,
            ratio = cropedWidth / cropedHeight,
            selectWidth = (cropedWidth) * (naturalWidth/scaledWidth),
            selectHeight = (cropedHeight) * (naturalHeight/scaledHeight);
        /*$picture.css('height', scaledHeight);
*/
        this.img = $.Jcrop($picture, {
            trueSize: [naturalWidth, naturalHeight],
            setSelect: [0, 0, selectWidth, selectHeight],
            aspectRatio: ratio,
            keySupport: false,
            allowSelect: false,
            onSelect: function(c) {
                self.onSelect(c);
            }
        });
	}

	crop(postData){
        var self = this;
    	var cropImgUrl = app.imgCropUrl;

    	postData = postData || {};

    	postData = $.extend(this.img.tellScaled(), postData, {width: this.element.width(), height: this.element.height(), group: self.config.group});
        $.post(cropImgUrl, postData ,function(response){
            self.afterCrop(response);
        })
    }

    onSelect(c) {
		//override it
	}

	afterCrop(response) {
		//override it
	}
}

export default EsImageCrop;