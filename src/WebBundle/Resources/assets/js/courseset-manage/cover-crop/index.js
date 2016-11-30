import EsImageCrop from 'libs/js/es-image-crop.js';

class CoverCrop
{
	constructor() {
		this.init();
	}

	init(){
		let imageCrop = new EsImageCrop({
            element: "#courseset-picture-crop",
            // group: "course_set",
            cropedWidth: 480,
            cropedHeight: 270
        });
        imageCrop.afterCrop = function(response){
            let url = $("#upload-picture-btn").data("url");
            console.log('after crop : ', url, response);
            $.post(url, {images: JSON.stringify(response)}, function(){
                document.location.href=$("#upload-picture-btn").data("gotoUrl");
            });
        };

        console.log('imageCrop: ', imageCrop);

        $("#upload-picture-btn").click(function(e){
            e.stopPropagation();

            imageCrop.crop({
                imgs: {
                    large: [480, 270],
                    middle: [304, 171],
                    small: [96, 54],
                }
            });

        })

        $('.go-back').click(function(){
            history.go(-1);
        });
	}
}


new CoverCrop();