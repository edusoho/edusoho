class DetailEditor {
	constructor() {
		this.init();
	}

	init(){
		CKEDITOR.replace('summary', {
		  allowedContent: true,
		  toolbar: 'Detail',
		  filebrowserImageUploadUrl: $('#summary').data('imageUploadUrl')
		});

        $('#courseset-submit').click(function(evt){
            $(evt.currentTarget).button('loading');
            $('#courseset-detail-form').submit();
        });
	}
}

new DetailEditor();