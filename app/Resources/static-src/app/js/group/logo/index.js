import EsWebUploader from 'libs/js/es-webuploader.js';
import notify from 'common/notify';
console.log('test');
new EsWebUploader({
	element: '#group-save-btn',
	onUploadSuccess: function(file, response) {
		let url = $("#group-save-btn").data("gotoUrl");
		notify('success', Translator.trans('上传成功！'), 1);
		document.location.href = url;
	}
});